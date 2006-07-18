<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviExecutionFilter is the last filter registered for each filter chain.
 * This filter does all action and view execution.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviExecutionFilter extends AgaviFilter implements AgaviIActionFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain The filter chain.
	 * @param      AgaviResponse A Response instance.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs during
	 *                                                 View initialization.
	 * @throws     <b>AgaviViewException</b>           If an error occurs while
	 *                                                 executing the View.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response)
	{
		// get the context, controller and validator manager
		$controller = $this->context->getController();
		
		// get the current action instance
		$actionEntry = $controller->getActionStack()->getLastEntry();
		
		// get the current action information
		$moduleName = $actionEntry->getModuleName();
		
		
		// execute the Action and get the View to execute
		list($viewModule, $viewName) = $this->runAction($actionEntry);
		
		if($viewName === AgaviView::NONE) {
			// no View returned, so we don't render anything
			return;
		}

		// get the view instance
		$viewInstance = $controller->getView($viewModule, $viewName);

		// initialize the view
		$viewInstance->initialize($response, $actionEntry->getActionInstance()->getAttributes());
		
		$key = $this->context->getRequest()->lock();
		// view initialization completed successfully
		$executeMethod = 'execute' . $this->context->getName();
		if(!method_exists($viewInstance, $executeMethod)) {
			$executeMethod = 'execute';
		}
		$viewInstance->$executeMethod($actionEntry->getParameters());
		$this->context->getRequest()->unlock($key);
		
		$renderer = null;
		
		while(true) {
			$oti = $controller->getOutputTypeInfo();
			if($oti['renderer'] !== null) {
				$renderer = new $oti['renderer']();
				$renderer->initialize($this->context, $oti['renderer_parameters']);
				$renderer->setView($viewInstance);
				if(isset($oti['extension'])) {
					$renderer->setExtension($oti['extension']);
				}
				try {
					// run the pre-render check to see if the template is there
					$renderer->preRenderCheck();
					break;
				} catch(AgaviRenderException $e) {
					if(isset($oti['fallback'])) {
						// template not found, but there's a fallback specified, so let's try that one
						$controller->setOutputType($oti['fallback']);
					} else {
						throw $e;
					}
				}
			} else {
				$renderer = null;
				break;
			}
		}
		
		if($renderer !== null && $viewInstance->getTemplate() !== null) {
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($response, $fcfi['parameters']);

			$controller->loadFilters($filterChain, 'rendering');
			$controller->loadFilters($filterChain, 'rendering', $viewModule);

			// register the renderer as the last filter
			$filterChain->register($renderer);

			// go, go, go!
			$filterChain->execute();
		}
		
		if($controller->getRenderMode() == AgaviView::RENDER_VAR) {
			$actionEntry->setPresentation($response);
		}
	}
	
	/**
	 * Execute the Action
	 *
	 * @param      AgaviActionStackEntry The Action's ActionStackEntry.
	 *
	 * @return     mixed The processed View information returned by the Action.
	 *
	 * @throws     AgaviViewException If the returned View does not exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function runAction(AgaviActionStackEntry $actionEntry)
	{
		$viewName = null;
		
		$controller = $this->context->getController();
		$validatorManager = $this->context->getValidatorManager();
		// clear the validator manager for reuse
		$validatorManager->clear();
		
		// get the current action instance
		$actionEntry = $controller->getActionStack()->getLastEntry();
		$actionInstance = $actionEntry->getActionInstance();

		// get the current action information
		$moduleName = $actionEntry->getModuleName();
		$actionName = $actionEntry->getActionName();
		
		// get the (already formatted) request method
		$method = $this->context->getRequest()->getMethod();
		
		$useGenericMethods = false;
		$executeMethod = 'execute' . $method;
		if(!method_exists($actionInstance, $executeMethod)) {
			$executeMethod = 'execute';
			$useGenericMethods = true;
		}
		
		if($useGenericMethods && !method_exists($actionInstance, $executeMethod) ) {
			// this action will skip validation/execution for this method
			// get the default view
			$viewName = $actionInstance->getDefaultViewName();
		} else {
			// set default validated status
			$validated = true;

			// get the current action validation configuration
			$validationConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/validate/' . $actionName . '.ini';

			if(is_readable($validationConfig)) {
				// load validation configuration
				// do NOT use require_once
				$validationConfig = 'modules/' . $moduleName . '/validate/' . $actionName . '.ini';
				require(AgaviConfigCache::checkConfig($validationConfig));
			}

			// manually load validators
			$registerValidatorsMethod = 'register' . $method . 'Validators';
			if(!method_exists($actionInstance, $registerValidatorsMethod)) {
				$registerValidatorsMethod = 'registerValidators';
			}
			$actionInstance->$registerValidatorsMethod($validatorManager);

			// process validators
			$validated = $validatorManager->execute();

			$validateMethod = 'validate' . $method;
			if(!method_exists($actionInstance, $validateMethod)) {
				$validateMethod = 'validate';
			}

			// prevent access to Request::getParameters()
			$key = $this->context->getRequest()->lock();
			// process manual validation
			if($actionInstance->$validateMethod() && $validated) {
				// execute the action
				$viewName = $actionInstance->$executeMethod($actionEntry->getParameters());
			} else {
				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!method_exists($actionInstance, $handleErrorMethod)) {
					$handleErrorMethod = 'handleError';
				}
				$viewName = $actionInstance->$handleErrorMethod($actionEntry->getParameters());
			}
			$this->context->getRequest()->unlock($key);
		}
		
		if(is_array($viewName)) {
			// we're going to use an entirely different action for this view
			$viewModule = $viewName[0];
			$viewName   = $viewName[1];
		} elseif($viewName !== AgaviView::NONE) {
			// use a view related to this action
			$viewName = $actionName . $viewName;
			$viewModule = $moduleName;
		} else {
			$viewName = AgaviView::NONE;
			$viewModule = AgaviView::NONE;
		}
		
		if($viewName !== AgaviView::NONE && !$controller->viewExists($viewModule, $viewName)) {
			// the requested view doesn't exist
			$file = AgaviConfig::get('core.module_dir') . '/' . $viewModule . '/views/' . $viewName . 'View.class.php';
			$error = 'Module "%s" does not contain the view "%sView" or the file "%s" is unreadable';
			$error = sprintf($error, $viewModule, $viewName, $file);
			throw new AgaviViewException($error);
		}
		
		return array($viewModule, $viewName);
	}
}

?>
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
class AgaviExecutionFilter extends AgaviFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain The filter chain.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs during
	 *                                                 View initialization.
	 * @throws     <b>AgaviViewException</b>           If an error occurs while
	 *                                                 executing the View.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute ($filterChain)
	{
		// get the context, controller and validator manager
		$context = $this->getContext();
		$controller = $context->getController();
		$validatorManager = $context->getValidatorManager();
		// clear the validator manager for reuse
		$validatorManager->clear();

		// get the current action instance
		$actionEntry = $controller->getActionStack()->getLastEntry();
		$actionInstance = $actionEntry->getActionInstance();

		// get the current action information
		$moduleName = $actionEntry->getModuleName();
		$actionName = $actionEntry->getActionName();

		// get the request method
		$method = ucfirst(strtolower($context->getRequest()->getMethod()));
		
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

			// process manual validation
			if($actionInstance->$validateMethod() && $validated) {
				// execute the action
				$viewName = $actionInstance->$executeMethod();
			} else {
				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!method_exists($actionInstance, $handleErrorMethod)) {
					$handleErrorMethod = 'handleError';
				}
				$viewName = $actionInstance->$handleErrorMethod();
			}
		}

		if($viewName != null) {
			if(is_array($viewName)) {
				// we're going to use an entirely different action for this view
				$moduleName = $viewName[0];
				$viewName   = $viewName[1];
			} else {
				// use a view related to this action
				$viewName = $actionName . $viewName;
			}

			// display this view
			if(!$controller->viewExists($moduleName, $viewName)) {
				// the requested view doesn't exist
				$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName . 'View.class.php';
				$error = 'Module "%s" does not contain the view "%sView" or the file "%s" is unreadable';
				$error = sprintf($error, $moduleName, $viewName, $file);
				throw new AgaviViewException($error);
			}

			// get the view instance
			$viewInstance = $controller->getView($moduleName, $viewName);

			// initialize the view
			$viewInstance->initialize($context);
			// view initialization completed successfully
			$viewInstance->execute();
			
			$renderer = $viewInstance->getRenderer();
			
			// create a new filter chain
			$fcfi = $context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();

			$controller->loadFilters($filterChain, 'rendering');
			$controller->loadFilters($filterChain, 'rendering', $moduleName);

			// register the renderer as the last filter
			$filterChain->register($renderer);

			// go, go, go!
			$filterChain->execute();
			
			// get the data from the view (the renderer put it there)
			$viewData = $viewInstance->getData();
			
			if($controller->getRenderMode() == AgaviView::RENDER_VAR) {
				$actionEntry->setPresentation($viewData);
			}
		}
	}
}

?>
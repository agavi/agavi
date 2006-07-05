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
 * AgaviController directs application flow.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviController extends AgaviParameterHolder
{
	/**
	 * @var        int The maximum number of times this Controller will forward().
	 */
	protected $maxForwards  = 20;
	
	/**
	 * @var        int The render mode, see AgaviView RENDER_* constants.
	 */
	protected $renderMode   = AgaviView::RENDER_CLIENT;

	/**
	 * @var        AgaviActionStack An ActionStack instance.
	 */
	protected $actionStack = null;

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
	/**
	 * @var        array An array of filter instances for reuse.
	 */
	protected $filters = array(
		'global' => array(),
		'action' => array(
			'*' => null
		),
		'rendering' => array(
			'*' => null
		),
		'dispatch' => null,
		'execution' => null,
		'security' => null
	);
	
	/**
	 * @var        string The currently set Output Type.
	 */
	protected $outputType = null;
	
	/**
	 * @var        array An array of registered Output Types.
	 */
	protected $outputTypes = array();
	
	/**
	 * @var        AgaviResponse The Response instance for this Controller.
	 */
	protected $response = null;

	/**
	 * Retrieve the ActionStack.
	 *
	 * @return     AgaviActionStack the ActionStack instance
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionStack()
	{
		return $this->actionStack;
	}

	/**
	 * Indicates whether or not a module has a specific action.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     bool true, if the action exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function actionExists($moduleName, $actionName)
	{
		$actionName = str_replace('.', '/', $actionName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		return is_readable($file);
	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      array An associative array of parameters to be set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function dispatch($parameters = array())
	{
		try {
			
			$request = $this->context->getRequest();
			
			// match routes and set matched routes as request attributes
			$request->setAttributes($this->context->getRouting()->execute(), 'org.agavi.routing.matchedRoutes');
		
			if($parameters != null) {
				$request->setParametersByRef($parameters);
			}
		
			// determine our module and action
			$moduleName = $request->getParameter($request->getModuleAccessor());
			$actionName = $request->getParameter($request->getActionAccessor());
		
			if($moduleName == null) {
				// no module has been specified
				$moduleName = AgaviConfig::get('actions.default_module');
				$request->setParameter($request->getModuleAccessor(), $moduleName);
			}
			if($actionName == null) {
				// no action has been specified
				if ($this->actionExists($moduleName, 'Index')) {
					// an Index action exists
					$actionName = 'Index';
				} else {
					// use the default action
					$actionName = AgaviConfig::get('actions.default_action');
				}
				$request->setParameter($request->getActionAccessor(), $actionName);
			}
			
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($this->response, $fcfi['parameters']);
			
			$this->loadFilters($filterChain, 'global');
		
			// register the dispatch filter
			$filterChain->register($this->filters['dispatch']);
		
			// go, go, go!
			$filterChain->execute();
			
			$this->response->send();
			
		} catch (Exception $e) {
			AgaviException::printStackTrace($e, $this->context, $this->getResponse());
		}
	}

	/**
	 * Forward the request to another action.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If an invalid configuration 
	 *                                                setting has been found.
	 * @throws     <b>AgaviForwardException</b> If an error occurs while forwarding
	 *                                          the request.
	 * @throws     <b>AgaviInitializationException</b> If the action could not be
	 *                                                 initialized.
	 * @throws     <b>AgaviSecurityException</b> If the action requires security but
	 *                                           the user implementation is not of
	 *                                           type SecurityUser.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function forward($moduleName, $actionName)
	{

		$actionName = str_replace('.', '/', $actionName);
		$actionName = preg_replace('/[^a-z0-9\-_\/]+/i', '', $actionName);
		$moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);

		if($this->actionStack->getSize() >= $this->maxForwards) {
			throw new AgaviForwardException('Too many forwards have been detected for this request');
		}

		if(!AgaviConfig::get('core.available', false)) {
			// application is unavailable
			$this->context->getRequest()->setAttributes(array(
				'requested_module' => $moduleName,
				'requested_action' => $actionName
			), 'org.agavi.controller.forwards.unavailable');
			$moduleName = AgaviConfig::get('actions.unavailable_module');
			$actionName = AgaviConfig::get('actions.unavailable_action');

			if(!$this->actionExists($moduleName, $actionName)) {
				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: actions.unavailable_module "%s", actions.unavailable_action "%s"';
				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviConfigurationException($error);
			}

		} elseif(!$this->actionExists($moduleName, $actionName)) {
			// the requested action doesn't exist

			// track the requested module so we have access to the data
			// in the error 404 page
			$this->context->getRequest()->setAttributes(array(
				'requested_module' => $moduleName,
				'requested_action' => $actionName
			), 'org.agavi.controller.forwards.error_404');

			// switch to error 404 action
			$moduleName = AgaviConfig::get('actions.error_404_module');
			$actionName = AgaviConfig::get('actions.error_404_action');

			if(!$this->actionExists($moduleName, $actionName)) {
				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: actions.error_404_module "%s", actions.error_404_action "%s"';
				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviConfigurationException($error);
			}
		}

		// create an instance of the action
		$actionInstance = $this->getAction($moduleName, $actionName);

		// add a new action stack entry
		$this->actionStack->addEntry($moduleName, $actionName, $actionInstance);

		// include the module configuration
		// laoded only once due to the way import() works
		if(is_readable(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml')) {
			AgaviConfigCache::import(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml', $this->context->getName());
		} else {
			AgaviConfig::set('modules.' . strtolower($moduleName) . '.enabled', true);
		}

		// save autoloads so we can restore them later
		$oldAutoloads = Agavi::$autoloads;
		
		static $moduleAutoloads = array();
		if(!isset($moduleAutoloads[$moduleName])) {
			$moduleAutoloads[$moduleName] = array();
			$moduleAutoload = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/autoload.xml';
			if(is_readable($moduleAutoload)) {
				include(AgaviConfigCache::checkConfig($moduleAutoload));
				$moduleAutoloads[$moduleName] = Agavi::$autoloads;
			}
		} else {
			Agavi::$autoloads = array_merge($moduleAutoloads[$moduleName], Agavi::$autoloads);
		}
		
		if(AgaviConfig::get('modules.' . strtolower($moduleName) . '.enabled')) {
			// check for a module config.php
			$moduleConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config.php';
			if(is_readable($moduleConfig)) {
				require_once($moduleConfig);
			}

			// initialize the action
			$actionInstance->initialize($this->context);
			
			// create a new response instance for this action
			$rfi = $this->context->getFactoryInfo('response');
			$response = new $rfi['class'];
			$response->initialize($this->context, $rfi['parameters']);

			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($response, $fcfi['parameters']);

			if(AgaviConfig::get('core.available', false)) {
				// the application is available so we'll register
				// global and module filters, otherwise skip them

				// does this action require security?
				if(AgaviConfig::get('core.use_security', false) && $actionInstance->isSecure()) {
					// register security filter
					$filterChain->register($this->filters['security']);
				}

				// load filters
				$this->loadFilters($filterChain, 'action');
				$this->loadFilters($filterChain, 'action', $moduleName);
			}

			// register the execution filter
			$filterChain->register($this->filters['execution']);

			// process the filter chain
			$filterChain->execute();
			
			if($this->renderMode == AgaviView::RENDER_CLIENT) {
				// add the output for this action to the global one
				$this->getResponse()->append($response->export());
			}
			
			// restore autoloads
			Agavi::$autoloads = $oldAutoloads;

		} else {
			// module is disabled
			$this->context->getRequest()->setAttributes(array(
				'requested_module' => $moduleName,
				'requested_action' => $actionName
			), 'org.agavi.controller.forwards.disabled');
			$moduleName = AgaviConfig::get('actions.module_disabled_module');
			$actionName = AgaviConfig::get('actions.module_disabled_action');

			if(!$this->actionExists($moduleName, $actionName)) {
				// cannot find mod disabled module/action
				$error = 'Invalid configuration settings: actions.module_disabled_module "%s", actions.module_disabled_action "%s"';
				$error = sprintf($error, $moduleName, $actionName);
				throw new AgaviConfigurationException($error);
			}

			$this->forward($moduleName, $actionName);
		}
	}

	/**
	 * Retrieve the currently executing Action's name.
	 *
	 * @return     string The currently executing action name, if one is set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionName()
	{
		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return $actionEntry->getActionName();
	}
	
	/**
	 * Retrieve the currently executing Action's module directory.
	 *
	 * @return     string An absolute filesystem path to the directory of the
	 *                    currently executing module if set, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleDirectory()
	{
		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return AgaviConfig::get('core.module_dir') . '/' . $actionEntry->getModuleName();
	}

	/**
	 * Retrieve the currently executing Action's module name.
	 *
	 * @return     string The currently executing module name, if one is set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleName()
	{
		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return $actionEntry->getModuleName();
	}

	/**
	 * Retrieve an Action implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     AgaviAction An Action implementation instance, if the action 
	 *                         exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getAction($moduleName, $actionName)
	{
		$actionName = str_replace('.', '/', $actionName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';

		if(file_exists($file)) {
			require_once($file);
		}

		$longActionName = $actionName;

		// Nested action check?
		$position = strrpos($actionName, '/');
		if($position > -1) {
			$longActionName = str_replace('/', '_', $actionName);
			$actionName = substr($actionName, $position + 1);
		}

		if(class_exists($moduleName . '_' . $longActionName . 'Action', false)) {
			$class = $moduleName . '_' . $longActionName . 'Action';
		} elseif(class_exists($moduleName . '_' . $actionName . 'Action', false)) {
			$class = $moduleName . '_' . $actionName . 'Action';
		} elseif(class_exists($longActionName . 'Action', false)) {
			$class = $longActionName . 'Action';
		} elseif(class_exists($actionName . 'Action')) {
			$class = $actionName . 'Action';
		} else {
			throw new AgaviException('Could not find Action "' . $longActionName . '" for module "' . $moduleName . '"');
		}

		return new $class();
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the Response object.
	 *
	 * @return     AgaviResponse The current Response implementation instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected final function getResponse()
	{
		return $this->response;
	}

	/**
	 * Retrieve the presentation rendering mode.
	 *
	 * @return     int One of the following:
	 *                 - AgaviView::RENDER_CLIENT
	 *                 - AgaviView::RENDER_VAR
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getRenderMode()
	{
		return $this->renderMode;
	}

	/**
	 * Retrieve a View implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     AgaviView A View implementation instance, if the model exists,
	 *                       otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getView($moduleName, $viewName)
	{
		$viewName = str_replace('.', '/', $viewName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName . 'View.class.php';

		require_once($file);

		$longViewName = $viewName;

		$position = strrpos($viewName, '/');
		if($position > -1) {
			$longViewName = str_replace('/', '_', $viewName);
			$viewName = substr($viewName, $position + 1);
		}

		if(class_exists($moduleName . '_' . $longViewName . 'View', false)) {
			$class = $moduleName . '_' . $longViewName . 'View';
		} elseif(class_exists($moduleName . '_' . $viewName . 'View', false)) {
			$class = $moduleName . '_' . $viewName . 'View';
		} elseif(class_exists($longViewName . 'View', false)) {
			$class = $longViewName . 'View';
		} elseif(class_exists($viewName . 'View', false)) {
			$class = $viewName . 'View';
		} else {
			throw new AgaviException('Could not find View "' . $longViewName . '" for module "' . $moduleName . '"');
		}

		return new $class();
	}

	/**
	 * Initialize this controller.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		$this->maxForwards = isset($parameters['max_fowards']) ? $parameters['max_forwards'] : 20;
		
		$this->context = $context;
		
		$asfi = $context->getFactoryInfo('action_stack');
		$this->actionStack = new $asfi['class']();
		
		$rfi = $this->context->getFactoryInfo('response');
		$this->response = new $rfi['class']();
		$this->response->initialize($this->context, $rfi['parameters']);
		
		$cfg = AgaviConfig::get('core.config_dir') . '/output_types.xml';
		require(AgaviConfigCache::checkConfig($cfg, $context->getName()));
		
		if(AgaviConfig::get('core.use_security', false)) {
			$sffi = $context->getFactoryInfo('security_filter');
			$this->filters['security'] = new $sffi['class']();
			$this->filters['security']->initialize($this->context, $sffi['parameters']);
		}
		
		$dffi = $this->context->getFactoryInfo('dispatch_filter');
		$this->filters['dispatch'] = new $dffi['class']();
		$this->filters['dispatch']->initialize($this->context, $dffi['parameters']);
		
		$effi = $this->context->getFactoryInfo('execution_filter');
		$this->filters['execution'] = new $effi['class']();
		$this->filters['execution']->initialize($this->context, $effi['parameters']);
		
	}
	
	/**
	 * Load filters.
	 *
	 * @param      AgaviFilterChain A FilterChain instance.
	 * @param      string           "global", "action" or "rendering".
	 * @param      string           A module name, or "*" for the generic config.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function loadFilters(AgaviFilterChain $filterChain, $which = 'global', $module = null)
	{
		if($module === null) {
			$module = '*';
		}
		
		if(($which != 'global' && !isset($this->filters[$which][$module])) || $which == 'global' && $this->filters[$which] == null) {
			if($which == 'global') {
				$this->filters[$which] = array();
				$filters =& $this->filters[$which];
			} else {
				$this->filters[$which][$module] = array();
				$filters =& $this->filters[$which][$module];
			}
			$config = ($module == '*' ? AgaviConfig::get('core.config_dir') : AgaviConfig::get('core.module_dir') . '/' . $module . '/config') . '/' . $which . '_filters.xml';
			if(is_readable($config)) {
				require(AgaviConfigCache::checkConfig($config, $this->context->getName()));
			}
		} else {
			if($which == 'global') {
				$filters =& $this->filters[$which];
			} else {
				$filters =& $this->filters[$which][$module];
			}
		}
		
		foreach($filters as $filter) {
			$filterChain->register($filter);
		}
	}

	/**
	 * Indicates whether or not a module has a specific model.
	 *
	 * @param      string A module name.
	 * @param      string A model name.
	 *
	 * @return     bool true, if the model exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function modelExists($moduleName, $modelName)
	{
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/models/' . $modelName .	'Model.class.php';

		return is_readable($file);
	}

	/**
	 * Indicates whether or not a module exists.
	 *
	 * @param      string A module name.
	 *
	 * @return     bool true, if the module exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function moduleExists($moduleName)
	{
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml';

		return is_readable($file);
	}

	/**
	 * Set the presentation rendering mode.
	 *
	 * @param      int A rendering mode.
	 *
	 * @throws     <b>AgaviRenderException</b> - If an invalid render mode has been 
	 *                                           set.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setRenderMode($mode)
	{
		if($mode == AgaviView::RENDER_CLIENT || $mode == AgaviView::RENDER_VAR || $mode == AgaviView::RENDER_NONE) {
			$this->renderMode = $mode;
			return;
		}

		// invalid rendering mode type
		$error = 'Invalid rendering mode: %s';
		$error = sprintf($error, $mode);

		throw new AgaviRenderException($error);
	}

	/**
	 * Execute the shutdown procedure for this controller.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
	}

	/**
	 * Indicates whether or not a module has a specific view.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     bool true, if the view exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function viewExists($moduleName, $viewName)
	{
		$viewName = str_replace('.', '/', $viewName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName . 'View.class.php';
		return is_readable($file);
	}
	
	/**
	 * Sets an output type for this response.
	 *
	 * @param      string The output type name.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @throws     <b>AgaviException</b> If the given output type doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType($outputType)
	{
		if(isset($this->outputTypes[$outputType])) {
			if(!$this->getResponse()->isLocked()) {
				$this->outputType = $outputType;
				return true;
			}
			return false;
		} else {
			throw new AgaviException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
	
	/**
	 * Retrieves the output type name set for this response.
	 *
	 * @return     string The name of the output type.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}
	
	/**
	 * Retrieve configuration details about an output type.
	 *
	 * @param      string The output type name.
	 *
	 * @return     array An associative array of output type settings and params.
	 *
	 * @throws     <b>AgaviException</b> If the given output type doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputTypeInfo($outputType = null)
	{
		if($outputType === null) {
			$outputType = $this->outputType;
		}
		if(isset($this->outputTypes[$outputType])) {
			return $this->outputTypes[$outputType];
		} else {
			throw new AgaviException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
}

?>
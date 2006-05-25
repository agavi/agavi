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

	private
		$maxForwards  = 20,
		$renderMode   = AgaviView::RENDER_CLIENT;

	protected
		$context      = null,
		$shutdownList = null,
		$outputType   = null,
		$outputTypes  = array(),
		$filters      = array(
			'global' => array(),
			'action' => array(
				'*' => null
			),
			'rendering' => array(
				'*' => null
			)
		);

	/**
	 * Sets an output type for this response.
	 *
	 * @param      string The output type name.
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviException</b> If the given output type doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType($outputType)
	{
		if(isset($this->outputTypes[$outputType])) {
			$this->outputType = $outputType;
			return;
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
	public function actionExists ($moduleName, $actionName)
	{
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		return is_readable($file);
	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      array An associative array of parameters to be set.
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function dispatch($parameters = array())
	{
		// create a new filter chain
		$fccn = $this->context->getClassName('filter_chain');
		$filterChain = new $fccn();
		
		$this->loadFilters($filterChain, 'global');
		
		// register the dispatch filter
		$dfcn = $this->context->getClassName('dispatch_filter');
		$dispatchFilter = new $dfcn();
		$dispatchFilter->initialize($this->context);
		$filterChain->register($dispatchFilter);
		
		// go, go, go!
		$filterChain->execute();
	}

	/**
	 * Forward the request to another action.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     void
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
	public function forward ($moduleName, $actionName)
	{

		$actionName = str_replace('.', '/', $actionName);
		$actionName = preg_replace('/[^a-z0-9\-_\/]+/i', '', $actionName);
		$moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);

		if ($this->getActionStack()->getSize() >= $this->maxForwards) {
			throw new AgaviForwardException('Too many forwards have been detected for this request');
		}

		if (!AgaviConfig::get('core.available', false)) {

			// application is unavailable
			$moduleName = AgaviConfig::get('actions.unavailable_module');
			$actionName = AgaviConfig::get('actions.unavailable_action');

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: ' .
						 'actions.unavailable_module "%s", ' .
						 'actions.unavailable_action "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviConfigurationException($error);

			}

		} else if (!$this->actionExists($moduleName, $actionName))
		{

			// the requested action doesn't exist

			// track the requested module so we have access to the data
			// in the error 404 page
			$this->context->getRequest()->setAttribute('requested_action', $actionName);
			$this->context->getRequest()->setAttribute('requested_module', $moduleName);

			// switch to error 404 action
			$moduleName = AgaviConfig::get('actions.error_404_module');
			$actionName = AgaviConfig::get('actions.error_404_action');

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: ' .
						 'actions.error_404_module "%s", ' .
						 'actions.error_404_action "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviConfigurationException($error);

			}

		}

		// create an instance of the action
		$actionInstance = $this->getAction($moduleName, $actionName);

		// add a new action stack entry
		$this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance);

		// include the module configuration
		AgaviConfigCache::import(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml');
		
		$oldAutoloads = null;
		$moduleAutoload = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/autoload.xml';
		if(is_readable($moduleAutoload)) {
			$oldAutoloads = Agavi::$autoloads;
			include(AgaviConfigCache::checkConfig($moduleAutoload));
		}

		if(AgaviConfig::get('modules.' . strtolower($moduleName) . '.enabled')) {

			// check for a module config.php
			$moduleConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config.php';
			if (is_readable($moduleConfig)) {
				require_once($moduleConfig);
			}

			// initialize the action
			if ($actionInstance->initialize($this->context)) {

				// create a new filter chain
				$fccn = $this->context->getClassName('filter_chain');
				$filterChain = new $fccn();

				if(AgaviConfig::get('core.available', false)) {
					// the application is available so we'll register
					// global and module filters, otherwise skip them

					// does this action require security?
					if(AgaviConfig::get('core.use_security', false) && $actionInstance->isSecure()) {

						if (!($this->context->getUser() instanceof AgaviSecurityUser)) {
							$error = 'Security is enabled, but your User ' .
							         'implementation isn\'t a sub-class of ' .
							         'SecurityUser';

							throw new AgaviSecurityException($error);

						}

						// register security filter
						$filterChain->register($this->context->getSecurityFilter());

					}

					// load filters
					$this->loadFilters($filterChain, 'action');
					$this->loadFilters($filterChain, 'action', $moduleName);

				}

				// register the execution filter
				$efcn = $this->context->getClassName('execution_filter');
				$execFilter = new $efcn();

				$execFilter->initialize($this->context);
				$filterChain->register($execFilter);

				// process the filter chain
				$filterChain->execute();

			} else
			{

				// action failed to initialize
				$error = 'Action initialization failed for module "%s", ' .
						 'action "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviInitializationException($error);

			}
			
			if($oldAutoloads !== null) {
				Agavi::$autoloads = $oldAutoloads;
			}

		} else
		{

			// module is disabled
			$moduleName = AgaviConfig::get('actions.module_disabled_module');
			$actionName = AgaviConfig::get('actions.module_disabled_action');

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find mod disabled module/action
				$error = 'Invalid configuration settings: ' .
						 'actions.module_disabled_module "%s", ' .
						 'actions.module_disabled_action "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new AgaviConfigurationException($error);

			}

			$this->forward($moduleName, $actionName);

		}

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
	public function getAction ($moduleName, $actionName)
	{
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';

		if (file_exists($file)) {
			require_once($file);
		}

		$longActionName = $actionName;

		// Nested action check?
		$position = strrpos($actionName, '/');
		if ($position > -1) {
			$longActionName = str_replace('/', '_', $actionName);
			$actionName = substr($actionName, $position + 1);
		}

		if (class_exists($moduleName . '_' . $longActionName . 'Action', false)) {
			$class = $moduleName . '_' . $longActionName . 'Action';
		} elseif (class_exists($moduleName . '_' . $actionName . 'Action', false)) {
			$class = $moduleName . '_' . $actionName . 'Action';
		} elseif (class_exists($longActionName . 'Action', false)) {
			$class = $longActionName . 'Action';
		} else {
			$class = $actionName . 'Action';
		}

		return new $class();

	}

	/**
	 * Retrieve the action stack.
	 *
	 * @return     AgaviActionStack An ActionStack instance, if the action stack is
	 *                              enabled, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getActionStack ()
	{

		return $this->context->getActionStack();

	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext A Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getContext ()
	{

		return $this->context;

	}

	/**
	 * Retrieve a global Model implementation instance.
	 *
	 * @param      string A model name.
	 *
	 * @return     AgaviModel A Model implementation instance, if the model exists,
	 *                        otherwise null. If the model implements an initialize
	 *                        method, it will be called with a Context instance.
	 *
	 * @throws     AgaviAutloadException if class is ultimately not found.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function getGlobalModel ($modelName)
	{

		$class = $modelName . 'Model';

		if (!class_exists($class, false)) {
			$file = AgaviConfig::get('core.lib_dir') . '/models/' . $modelName . 'Model.class.php';
			if (file_exists($file)) {
				require_once($file);
			} else {
				$pattern = AgaviConfig::get('core.lib_dir') . '/' . '*' . '/models/' . $modelName . 'Model.class.php';
				if ($files = glob($pattern)) {
					// only include the first file found
					require_once($files[0]);
				}
			}
		}

		// if the above code didnt find the class, allow autoload to fire as a last ditch attempt to find it
		if (class_exists($class)) {
			if (AgaviToolkit::isSubClass($class, 'AgaviSingletonModel')) {
				$model = call_user_func(array($class, 'getInstance'), $class);
			} else {
				$model = new $class();
			}
			if (method_exists($model, 'initialize')) {
				$model->initialize($this->context);
			}
			return $model;
		}
		// we'll never actually get here, but what the hay.
		return null;
	}

	/**
	 * Retrieve the singleton instance of this class.
	 *
	 * @return     AgaviController A Controller implementation instance.
	 *
	 * @throws     <b>AgaviControllerException</b> If a controller implementation
	 *                                             instance has not been created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 * @deprecated
	 */
	public static function getInstance ()
	{
		$error = 'AgaviController::getInstance deprecated, use newInstance method instead.';
		throw new AgaviControllerException($error);
	}

	/**
	 * Retrieve a Model implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string A model name.
	 *
	 * @return     AgaviModel A Model implementation instance, if the model exists,
	 *                        otherwise null. If the model implements an initialize
	 *                        method, it will be called with a Context instance.
	 *
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function getModel ($moduleName, $modelName)
	{

		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/models/' . $modelName .	'Model.class.php';
		require_once($file);

		$class = $modelName . 'Model';

		// fix for same name classes
		$moduleClass = $moduleName . '_' . $class;

		if (class_exists($moduleClass, false)) {
			$class = $moduleClass;
		}

		if (AgaviToolkit::isSubClass($class, 'AgaviSingletonModel')) {
			$model = call_user_func(array($class, 'getInstance'), $class);
		} else {
			$model = new $class();
		}

		if (method_exists($model, 'initialize')) {
			$model->initialize($this->context);
		}

		return $model;

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
	public function getRenderMode ()
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
	public function getView ($moduleName, $viewName)
	{

		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName .
				'View.class.php';

		require_once($file);

		$longViewName = $viewName;

		$position = strrpos($viewName, '/');
		if ($position > -1)
		{
			$longViewName = str_replace('/', '_', $viewName);
			$viewName = substr($viewName, $position + 1);
		}


		if (class_exists($moduleName . '_' . $longViewName . 'View', false)) {
			$class = $moduleName . '_' . $longViewName . 'View';
		} elseif (class_exists($moduleName . '_' . $viewName . 'View', false)) {
			$class = $moduleName . '_' . $viewName . 'View';
		} elseif (class_exists($longViewName . 'View', false)) {
			$class = $longViewName . 'View';
		} else {
			$class = $viewName . 'View';
		}

		return new $class();

	}

	/**
	 * Initialize this controller.
	 *
	 * @param      AgaviContext object
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function initialize (AgaviContext $context)
	{
		$this->maxForwards = AgaviConfig::get('controller.max_forwards', 20);
		$this->context = $context;
		
		$cfg = AgaviConfig::get('core.config_dir') . '/output_types.xml';
		require_once(AgaviConfigCache::checkConfig($cfg, $context->getName()));

		register_shutdown_function(array($this, 'shutdown'));
	}
	
	/**
	 * Load filters.
	 *
	 * @param      AgaviFilterChain A FilterChain instance.
	 * @param      string           "global", "action" or "rendering".
	 * @param      string           A module name, or "*" for the generic config.
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadFilters(AgaviFilterChain $filterChain, $which = 'global', $module = null)
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
				require_once(AgaviConfigCache::checkConfig($config));
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
	public function modelExists ($moduleName, $modelName)
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
	public function moduleExists ($moduleName)
	{

		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.ini';

		return is_readable($file);

	}

	/**
	 * Retrieve a new Controller implementation instance.
	 *
	 * @param      string A Controller implementation name.
	 *
	 * @return     AgaviController A Controller implementation instance.
	 *
	 * @throws     <b>AgaviFactoryException</b> If a new controller implementation
	 *                                          instance cannot be created.
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function newInstance ($class)
	{
		if (class_exists($class) && AgaviToolkit::isSubClass($class, 'AgaviController')) {
			return new $class();
		}
		$error = "Class ($class) doesn't exist or is not a Controller.";
		throw new AgaviFactoryException($error);
	}

	/**
	 * Set the presentation rendering mode.
	 *
	 * @param      int A rendering mode.
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviRenderException</b> - If an invalid render mode has been 
	 *                                           set.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setRenderMode ($mode)
	{

		if ($mode == AgaviView::RENDER_CLIENT || $mode == AgaviView::RENDER_VAR ||
			$mode == AgaviView::RENDER_NONE)
		{

			$this->renderMode = $mode;

			return;

		}

		// invalid rendering mode type
		$error = 'Invalid rendering mode: %s';
		$error = sprintf($error, $mode);

		throw new AgaviRenderException($error);

	}

	/**
	 * Register a shutdown listener.
	 * The object is notified when Controller is shutdown.
	 *
	 * All registered listeners are notified before framework core classes are 
	 * shutdown so among others User, Request and Database are at you disposal
	 * during shutdown.
	 *
	 * If you register an object twice it will be notified twice.
	 *
	 * @param      AgaviShutdownListener
	 *
	 * @return     void
	 *
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function registerShutdownListener (AgaviShutdownListener $obj)
	{
		$this->shutdownList[] = $obj;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * All registered ShutdownListeners are notified before framework core
	 * classes are shutdown.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{
		//notify shutdown listeners
		if (is_array($this->shutdownList)) {
			foreach ($this->shutdownList as $sdListener) {
				$sdListener->shutdown();
			}
		}

		if ($user = $this->context->getUser()) {
			$user->shutdown();
		}

		session_write_close();
		$this->context->getStorage()->shutdown();
		$this->context->getRequest()->shutdown();

		if (AgaviConfig::get('core.use_database')) {
			$this->context->getDatabaseManager()->shutdown();
		}

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
	public function viewExists ($moduleName, $viewName)
	{

		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName .
				'View.class.php';

		return is_readable($file);

	}

	/**
	 * Indicates whether or not we were called using the CLI version of PHP.
	 *
	 * @return     bool true, if we're using cli, otherwise false.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      1.0
	 */
	public function inCLI()
	{
		return php_sapi_name() == 'cli';
	}

}

?>
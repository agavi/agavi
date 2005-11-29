<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
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
 * Controller directs application flow.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
abstract class Controller extends ParameterHolder
{

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+

	private
		$maxForwards     = 20,
		$renderMode      = View::RENDER_CLIENT,
		$executionFilterClassName = null;

	protected
		$context         = null;


	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+
	
	/**
	 *
	 * The dispatch method must be implemented
	 * it's expected to:
	 *		put and parameters into the request object
	 *		call the controller's initialize method
	 *		forward to the requested module/action
	 */
	abstract function dispatch();
	
	/**
	 * Indicates whether or not a module has a specific action.
	 *
	 * @param string A module name.
	 * @param string An action name.
	 *
	 * @return bool true, if the action exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function actionExists ($moduleName, $actionName)
	{
		$file = AG_MODULE_DIR . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		return is_readable($file);
	}

	// -------------------------------------------------------------------------

	/**
	 * Forward the request to another action.
	 *
	 * @param string A module name.
	 * @param string An action name.
	 *
	 * @return void
	 *
	 * @throws <b>ConfigurationException</b> If an invalid configuration setting
	 *                                       has been found.
	 * @throws <b>ForwardException</b> If an error occurs while forwarding the
	 *                                 request.
	 * @throws <b>InitializationException</b> If the action could not be
	 *                                        initialized.
	 * @throws <b>SecurityException</b> If the action requires security but
	 *                                  the user implementation is not of type
	 *                                  SecurityUser.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function forward ($moduleName, $actionName)
	{

		$actionName = str_replace('.', '/', $actionName);
		$actionName = preg_replace('/[^a-z0-9\-_\/]+/i', '', $actionName);
		$moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);

		if ($this->getActionStack()->getSize() >= $this->maxForwards) {
			throw new ForwardException('Too many forwards have been detected for this request');
		}

		if (!AG_AVAILABLE) {

			// application is unavailable
			$moduleName = AG_UNAVAILABLE_MODULE;
			$actionName = AG_UNAVAILABLE_ACTION;

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: ' .
						 'AG_UNAVAILABLE_MODULE "%s", ' .
						 'AG_UNAVAILABLE_ACTION "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new ConfigurationException($error);

			}

		} else if (!$this->actionExists($moduleName, $actionName))
		{

			// the requested action doesn't exist

			// track the requested module so we have access to the data
			// in the error 404 page
			$this->context->getRequest()->setAttribute('requested_action', $actionName);
			$this->context->getRequest()->setAttribute('requested_module', $moduleName);

			// switch to error 404 action
			$moduleName = AG_ERROR_404_MODULE;
			$actionName = AG_ERROR_404_ACTION;

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find unavailable module/action
				$error = 'Invalid configuration settings: ' .
						 'AG_ERROR_404_MODULE "%s", ' .
						 'AG_ERROR_404_ACTION "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new ConfigurationException($error);

			}

		}

		// create an instance of the action
		$actionInstance = $this->getAction($moduleName, $actionName);
		
		// add a new action stack entry
		$this->getActionStack()->addEntry($moduleName, $actionName, $actionInstance);

		// include the module configuration
		ConfigCache::import(AG_MODULE_DIR . '/' . $moduleName . '/config/module.ini');
		$enabled_str = 'MOD_' . strtoupper($moduleName) . '_ENABLED';
		if (defined($enabled_str) && constant($enabled_str)) {
			
			// check for a module config.php
			$moduleConfig = AG_MODULE_DIR . '/' . $moduleName . '/config.php';
			if (is_readable($moduleConfig)) {
				require_once($moduleConfig);
			}

			// initialize the action
			if ($actionInstance->initialize($this->context)) {

				// create a new filter chain
				$filterChain = new FilterChain();

				if (AG_AVAILABLE) {
					// the application is available so we'll register
					// global and module filters, otherwise skip them

					// does this action require security?
					if (AG_USE_SECURITY && $actionInstance->isSecure()) {

						if (!($this->context->getUser() instanceof SecurityUser)) {
							$error = 'Security is enabled, but your User ' .
							         'implementation isn\'t a sub-class of ' .
							         'SecurityUser';
							
							throw new SecurityException($error);

						}

						// register security filter
						$filterChain->register($this->context->getSecurityFilter());

					}
					
					// load filters
					$this->loadGlobalFilters($filterChain);
					$this->loadModuleFilters($filterChain);

				}

				// register the execution filter
				$execFilter = new $this->executionFilterClassName();

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

				throw new InitializationException($error);

			}

		} else
		{

			// module is disabled
			$moduleName = AG_MODULE_DISABLED_MODULE;
			$actionName = AG_MODULE_DISABLED_ACTION;

			if (!$this->actionExists($moduleName, $actionName))
			{

				// cannot find mod disabled module/action
				$error = 'Invalid configuration settings: ' .
						 'AG_MODULE_DISABLED_MODULE "%s", ' .
						 'AG_MODULE_DISABLED_ACTION "%s"';

				$error = sprintf($error, $moduleName, $actionName);

				throw new ConfigurationException($error);

			}

			$this->forward($moduleName, $actionName);

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an Action implementation instance.
	 *
	 * @param string A module name.
	 * @param string An action name.
	 *
	 * @return Action An Action implementation instance, if the action exists,
	 *                otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function getAction ($moduleName, $actionName)
	{
		$file = AG_MODULE_DIR . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		
		if (file_exists($file)) {
			require_once($file);
		}
		
		// Nested action check?
		$position = strrpos($actionName, '/');
		if ($position > -1) {
			$actionName = substr($actionName, $position + 1);
		}

		if (class_exists($moduleName . '_' . $actionName . 'Action', false)) {
			$class = $moduleName . '_' . $actionName . 'Action';
		} else {
			$class = $actionName . 'Action';
		}

		return new $class();

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the action stack.
	 *
	 * @return ActionStack An ActionStack instance, if the action stack is
	 *                     enabled, otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getActionStack ()
	{

		return $this->context->getActionStack();

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the current application context.
	 *
	 * @return Context A Context instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getContext ()
	{

		return $this->context;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a global Model implementation instance.
	 *
	 * @param string A model name.
	 *
	 * @return Model A Model implementation instance, if the model exists,
	 *               otherwise null. If the model implements an initialize
	 *               method, it will be called with an instance of the Context.
	 *
	 * @throws AutloadException if class is ultimately not found.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author David Zuelke (dz@bitxtender.com)
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function getGlobalModel ($modelName)
	{

		$class = $modelName . 'Model';
		
		if (!class_exists($class, false)) {
			$file = AG_LIB_DIR . '/models/' . $modelName . 'Model.class.php';
			if (file_exists($file)) {
				require_once($file);
			} else {
				$pattern = AG_LIB_DIR . '/' . '*' . '/models/' . $modelName . 'Model.class.php';
				if ($files = glob($pattern)) {
					// only include the first file found
					require_once($files[0]);
				}
			}
		}

		// if the above code didnt find the class, allow autoload to fire as a last ditch attempt to find it
		if (class_exists($class)) {
			if (Toolkit::isSubClass($class, 'SingletonModel')) {
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

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the singleton instance of this class.
	 *
	 * @return Controller A Controller implementation instance.
	 *
	 * @throws <b>ControllerException</b> If a controller implementation
	 *                                    instance has not been created.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 * @deprecated
	 */
	public static function getInstance ()
	{
		$error = 'Controller::getInstance deprecated, use newInstance method instead.';
		throw new ControllerException($error);
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a Model implementation instance.
	 *
	 * @param string A module name.
	 * @param string A model name.
	 *
	 * @return Model A Model implementation instance, if the model exists,
	 *               otherwise null. If the model implements an initialize
	 *               method, it will be called with an instance of the Context.
	 *
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author David Zuelke (dz@bitxtender.com)
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function getModel ($moduleName, $modelName)
	{

		$file = AG_MODULE_DIR . '/' . $moduleName . '/models/' . $modelName .	'Model.class.php';
		require_once($file);

		$class = $modelName . 'Model';

		// fix for same name classes
		$moduleClass = $moduleName . '_' . $class;

		if (class_exists($moduleClass, false)) {
			$class = $moduleClass;
		}

		if (Toolkit::isSubClass($class, 'SingletonModel')) {
			$model = call_user_func(array($class, 'getInstance'), $class);
		} else {
			$model = new $class();
		}

		if (method_exists($model, 'initialize')) {
			$model->initialize($this->context);
		}

		return $model;
			
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the presentation rendering mode.
	 *
	 * @return int One of the following:
	 *             - View::RENDER_CLIENT
	 *             - View::RENDER_VAR
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getRenderMode ()
	{

		return $this->renderMode;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a View implementation instance.
	 *
	 * @param string A module name.
	 * @param string A view name.
	 *
	 * @return View A View implementation instance, if the model exists,
	 *              otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getView ($moduleName, $viewName)
	{

		$file = AG_MODULE_DIR . '/' . $moduleName . '/views/' . $viewName .
				'View.class.php';

		require_once($file);

		$position = strrpos($viewName, '/');

		if ($position > -1)
		{

			$viewName = substr($viewName, $position + 1);

		}

		$class = $viewName . 'View';

		// fix for same name classes
		$moduleClass = $moduleName . '_' . $class;

		if (class_exists($moduleClass, false))
		{

			$class = $moduleClass;

		}

		return new $class();

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialize this controller.
	 *
	 * @param Context object
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function initialize (Context $context)
	{
		$this->maxForwards = defined('AG_MAX_FORWARDS') ? AG_MAX_FORWARDS : 20;
		$this->context = $context;
		
		register_shutdown_function(array($this, 'shutdown'));
	}

	// -------------------------------------------------------------------------

	/**
	 * Set the name of the ExecutionFilter class that is used in forward()
	 *
	 * @param string The class name of the ExecutionFilter to use
	 *
	 * @return void
	 *
	 * @author David Zuelke (dz@bitxtender.com)
	 * @since  0.10.0
	 */
	public function setExecutionFilterClassName($className)
	{
		$this->executionFilterClassName = $className;
	}

	// -------------------------------------------------------------------------

	/**
	 * Load global filters.
	 *
	 * @param FilterChain A FilterChain instance.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	private function loadGlobalFilters ($filterChain)
	{

		static $list = array();

		// grab our global filter ini and preset the module name
		$config     = AG_CONFIG_DIR . '/filters.ini';
		$moduleName = 'global';

		if (!isset($list[$moduleName])) {
			if (is_readable($config))	{
				// load global filters
				require_once(ConfigCache::checkConfig('config/filters.ini'));
			} else {
				$list[$moduleName] = array();
			}
		}

		// register filters
		foreach ($list[$moduleName] as $filter)	{
			$filterChain->register($filter);
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Load module filters.
	 *
	 * @param FilterChain A FilterChain instance.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	private function loadModuleFilters ($filterChain)
	{

		// filter list cache file
		static $list = array();

		// get the module name
		$moduleName = $this->context->getModuleName();

		if (!isset($list[$moduleName]))	{
			// we haven't loaded a filter list for this module yet
			$config = AG_MODULE_DIR . '/' . $moduleName . '/config/filters.ini';
			if (is_readable($config)) {
				require_once(ConfigCache::checkConfig($config));
			} else {
				// add an emptry array for this module since no filters
				// exist
				$list[$moduleName] = array();
			}
		}

		// register filters
		foreach ($list[$moduleName] as $filter)	{
			$filterChain->register($filter);
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not a module has a specific model.
	 *
	 * @param string A module name.
	 * @param string A model name.
	 *
	 * @return bool true, if the model exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function modelExists ($moduleName, $modelName)
	{

		$file = AG_MODULE_DIR . '/' . $moduleName . '/models/' . $modelName .	'Model.class.php';

		return is_readable($file);

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not a module exists.
	 *
	 * @param string A module name.
	 *
	 * @return bool true, if the module exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function moduleExists ($moduleName)
	{

		$file = AG_MODULE_DIR . '/' . $moduleName . '/config/module.ini';

		return is_readable($file);

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a new Controller implementation instance.
	 *
	 * @param string A Controller implementation name.
	 *
	 * @return Controller A Controller implementation instance.
	 *
	 * @throws <b>FactoryException</b> If a new controller implementation
	 *                                 instance cannot be created.
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public static function newInstance ($class)
	{
		if (class_exists($class) && Toolkit::isSubClass($class, 'Controller')) {
			return new $class();
		} 
		$error = "Class ($class) doesnt exist or is not a Controller.";
		throw new FactoryException($error);
	}

	// -------------------------------------------------------------------------

	/**
	 * Set the presentation rendering mode.
	 *
	 * @param int A rendering mode.
	 *
	 * @return void
	 *
	 * @throws <b>RenderException</b> - If an invalid render mode has been set.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  2.0.0
	 */
	public function setRenderMode ($mode)
	{

		if ($mode == View::RENDER_CLIENT || $mode == View::RENDER_VAR ||
			$mode == View::RENDER_NONE)
		{

			$this->renderMode = $mode;

			return;

		}

		// invalid rendering mode type
		$error = 'Invalid rendering mode: %s';
		$error = sprintf($error, $mode);

		throw new RenderException($error);

	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function shutdown ()
	{
		if ($user = $this->context->getUser()) {
			$user->shutdown();
		}
		session_write_close();
		$this->context->getStorage()->shutdown();
		$this->context->getRequest()->shutdown();

		if (AG_USE_DATABASE) {
			$this->context->getDatabaseManager()->shutdown();
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not a module has a specific view.
	 *
	 * @param string A module name.
	 * @param string A view name.
	 *
	 * @return bool true, if the view exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function viewExists ($moduleName, $viewName)
	{

		$file = AG_MODULE_DIR . '/' . $moduleName . '/views/' . $viewName .
				'View.class.php';

		return is_readable($file);

	}

	/**
	 * Indicates whether or not we were called using the CLI version of PHP.
	 *
	 * @return bool true, if we're using cli, otherwise false.
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  1.0
	 */
	public function inCLI()
	{
		return php_sapi_name() == 'cli';
	}

}

?>

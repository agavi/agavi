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
 * AgaviContext provides information about the current application context, 
 * such as the module and action names and the module directory. 
 * It also serves as a gateway to the core pieces of the framework, allowing
 * objects with access to the context, to access other useful objects such as
 * the current controller, request, user, actionstack, databasemanager, storage,
 * and loggingmanager.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviContext
{
	/**
	 * @var        AgaviController A Controller instance.
	 */
	protected $controller = null;
	
	/**
	 * @var        array An array of class names for frequently used factories.
	 */
	protected $classNames = array(
		'execution_filter' => null,
		'filter_chain' => null,
		'security_filter' => null
	);
	
	/**
	 * @var        AgaviDatabaseManager A DatabaseManager instance.
	 */
	protected $databaseManager = null;
	
	/**
	 * @var        AgaviLoggerManager A LoggerManager instance.
	 */
	protected $loggerManager = null;
	
	/**
	 * @var        AgaviRequest A Request instance.
	 */
	protected $request = null;
	
	/**
	 * @var        AgaviRouting A Routing instance.
	 */
	protected $routing = null;
	
	/**
	 * @var        AgaviSecurityFilter A SecurityFilter instance.
	 */
	protected $securityFilter = null;
	
	/**
	 * @var        AgaviStorage A Storage instance.
	 */
	protected $storage = null;
	
	/**
	 * @var        AgaviUser A User instance.
	 */
	protected $user = null;
	
	/**
	 * @var        AgaviValidatorManager A ValidatorManager instance.
	 */
	protected $validatorManager = null;
	
	/**
	 * @var        array An array of Context instances.
	 */
	protected static $instances = null;

	/*
	 * Clone method, overridden to prevent cloning, there can be only one. 
	 *
	 * @author     Mike Vincent <mike@agavi.org>	
	 * @since      0.9.0
	 */
	public function __clone()
	{
		trigger_error('Cloning the Context object is not allowed.', E_USER_ERROR);
	}	

	/**
	 * Constuctor method, intentionally made private so the context cannot be 
	 * created directly.
	 *
	 * @author     Mike Vincent <mike@agavi.org>	
	 * @since      0.9.0
	 */
	protected function __construct() 
	{
		// Singleton, use Context::getInstance($controller) to get the instance
	}

	/**
	 * Get a class name for repeatedly used factories such as FilterChains.
	 *
	 * @param      string The factory identifier.
	 *
	 * @return     string The class name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getClassName($for)
	{
		return $this->classNames[$for];
	}

	/**
	 * Retrieve the controller.
	 *
	 * @return     AgaviController The current Controller implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Retrieve a database connection from the database manager.
	 *
	 * This is a shortcut to manually getting a connection from an existing
	 * database implementation instance.
	 *
	 * If the core.use_database setting is off, this will return null.
	 *
	 * @param      name A database name.
	 *
	 * @return     mixed An AgaviDatabase instance.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the requested database name does
	 *                                           not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabaseConnection($name = 'default')
	{
		if($this->databaseManager != null) {
			return $this->databaseManager->getDatabase($name)->getConnection();
		}
	}

	/**
	 * Retrieve the database manager.
	 *
	 * @return     AgaviDatabaseManager The current DatabaseManager instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabaseManager()
	{
		return $this->databaseManager;
	}

	/**
	 * Retrieve the Context instance.
	 *
	 * @param      string name corresponding to a section of the config
	 *
	 * @return     AgaviContext instance of the requested name
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public static function getInstance($profile = null)
	{
		try {
			if($profile === null) {
				$profile = AgaviConfig::get('core.default_context', 'stdctx');
			}
			$profile = strtolower($profile);
			if (!isset(self::$instances[$profile])) {
				$class = __CLASS__;
				self::$instances[$profile] = new $class;
				self::$instances[$profile]->initialize($profile);
			}
			return self::$instances[$profile];
		} catch (Exception $e) {
			AgaviException::printStackTrace($e);
		}
	}
	
	/**
	 * Retrieve the LoggerManager
	 *
	 * @return     AgaviLoggerManager The current LoggerManager implementation instance
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLoggerManager()
	{
		return $this->loggerManager;
	}

	/**
	 * (re)Initialize the Context instance.
	 *
	 * @param      string name corresponding to a section of the config
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.10.0
	 */
	public function initialize($profile = null)
	{
		if($profile === null) {
			$profile = AgaviConfig::get('core.default_context', 'stdctx');
		}
		
		$profile = strtolower($profile);
		
		$this->name = $profile;
		
		include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/factories.xml', $profile));
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
	 * Retrieve the name of this Context.
	 *
	 * @return     string A context name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Retrieve the request.
	 *
	 * @return     AgaviRequest The current Request implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Retrieve the routing.
	 *
	 * @return     AgaviRouting The current Routing implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRouting()
	{
		return $this->routing;
	}

	/**
	 * Retrieve the securityFilter
	 *
	 * @return     AgaviSecurityFilter The current SecurityFilter implementation 
	 *                                 instance.
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function getSecurityFilter()
	{
		return $this->securityFilter;
	}

	/**
	 * Retrieve the storage.
	 *
	 * @return     AgaviStorage The current Storage implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * Retrieve the user.
	 *
	 * @return     AgaviUser The current User implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * Retrieve the ValidatorManager
	 *
	 * @return     AgaviValidatorManager The current ValidatorManager implementation
	 *                                   instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getValidatorManager()
	{
		return $this->validatorManager;
	}
}

?>
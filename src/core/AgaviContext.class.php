<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * the current controller, request, user, database manager etc.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviContext
{
	/**
	 * @var        string The name of the Context.
	 */
	protected $name = '';
	
	/**
	 * @var        AgaviController A Controller instance.
	 */
	protected $controller = null;
	
	/**
	 * @var        array An array of class names for frequently used factories.
	 */
	protected $factories = array(
		'dispatch_filter' => null,
		'execution_container' => null,
		'execution_filter' => null,
		'filter_chain' => null,
		'response' => null,
		'security_filter' => null,
		'validation_manager' => null,
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
	 * @var        AgaviStorage A Storage instance.
	 */
	protected $storage = null;
	
	/**
	 * @var        AgaviTranslationManager A TranslationManager instance.
	 */
	protected $translationManager = null;
	
	/**
	 * @var        AgaviUser A User instance.
	 */
	protected $user = null;
	
	/**
	 * @var        array The array used for the shutdown sequence.
	 */
	protected $shutdownSequence = array();
	
	/**
	 * @var        array An array of AgaviContext instances.
	 */
	protected static $instances = array();
	
	/**
	 * @var        array An array of SingletonModel instances.
	 */
	protected $singletonModelInstances = array();

	/**
	 * Clone method, overridden to prevent cloning, there can be only one.
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function __clone()
	{
		trigger_error('Cloning an AgaviContext instance is not allowed.', E_USER_ERROR);
	}

	/**
	 * Constructor method, intentionally made protected so the context cannot be
	 * created directly.
	 *
	 * @param      string The name of this context.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	protected function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * __toString overload, returns the name of the Context.
	 *
	 * @return     string The context name.
	 *
	 * @see        AgaviContext::getName()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * Get information on a frequently used class.
	 *
	 * @param      string The factory identifier.
	 *
	 * @return     array An associative array (keys 'class' and 'parameters').
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFactoryInfo($for)
	{
		if(isset($this->factories[$for])) {
			return $this->factories[$for];
		}
	}

	/**
	 * Factory for frequently used classes from factories.xml
	 *
	 * @param      string The factory identifier.
	 *
	 * @return     mixed An instance, already initialized with parameters.
	 *
	 * @throws     AgaviException If no such identifier exists.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function createInstanceFor($for)
	{
		$info = $this->getFactoryInfo($for);
		if(null === $info) {
			throw new AgaviException(sprintf('No factory info for "%s"', $for));
		}
		
		$class = new $info['class']();
		$class->initialize($this, $info['parameters']);
		return $class;
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
	 * @return     mixed A database connection.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the requested database name 
	 *                                           does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabaseConnection($name = null)
	{
		if($this->databaseManager !== null) {
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
	 * Retrieve the AgaviContext instance.
	 *
	 * If you don't supply a profile name this will try to return the context 
	 * specified in the <kbd>core.default_context</kbd> setting.
	 *
	 * @param      string A name corresponding to a section of the config
	 *
	 * @return     AgaviContext An context instance initialized with the 
	 *                          settings of the requested context name
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public static function getInstance($profile = null)
	{
		try {
			if($profile === null) {
				$profile = AgaviConfig::get('core.default_context');
				if($profile === null) {
					throw new AgaviException('You must supply a context name to AgaviContext::getInstance() or set the name of the default context to be used in the configuration directive "core.default_context".');
				}
			}
			$profile = strtolower($profile);
			if(!isset(self::$instances[$profile])) {
				$class = AgaviConfig::get('core.context_implementation', __CLASS__);
				self::$instances[$profile] = new $class($profile);
				self::$instances[$profile]->initialize();
			}
			return self::$instances[$profile];
		} catch(Exception $e) {
			AgaviException::render($e);
		}
	}
	
	/**
	 * Retrieve the LoggerManager
	 *
	 * @return     AgaviLoggerManager The current LoggerManager implementation 
	 *                                instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLoggerManager()
	{
		return $this->loggerManager;
	}

	/**
	 * (re)Initialize the AgaviContext instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.10.0
	 */
	public function initialize()
	{
		try {
			include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/factories.xml', $this->name));
		} catch(Exception $e) {
			AgaviException::render($e, $this);
		}
		
		register_shutdown_function(array($this, 'shutdown'));
	}
	
	/**
	 * Shut down this AgaviContext and all related factories.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
		foreach($this->shutdownSequence as $object) {
			$object->shutdown();
		}
	}
	
	/**
	 * Retrieve a Model implementation instance.
	 *
	 * @param      string A model name.
	 * @param      string A module name, if the requested model is a module model,
	 *                    or null for global models.
	 * @param      array  An array of parameters to be passed to initialize() or
	 *                    the constructor.
	 *
	 * @return     AgaviModel A Model implementation instance.
	 *
	 * @throws     AgaviAutloadException if class is ultimately not found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModel($modelName, $moduleName = null, array $parameters = null)
	{
		$origModelName = $modelName;
		$modelName = AgaviToolkit::canonicalName($modelName);
		$class = str_replace('/', '_', $modelName) . 'Model';
		$file = null;
		$rc = null;
		
		if($moduleName === null) {
			// global model
			// let's try to autoload that baby
			if(!class_exists($class)) {
				// it's not there. the hunt is on
				$file = AgaviConfig::get('core.model_dir') . '/' . $modelName . 'Model.class.php';
			}
		} else {
			try {
				$this->controller->initializeModule($moduleName);
			} catch(AgaviDisabledModuleException $e) {
				// swallow, this will load the modules autoload but throw an exception 
				// if the module is disabled.
			}
			// module model
			// alternative name
			$class = $moduleName . '_' . $class;
			// let's try to autoload the baby
			if(!class_exists($class)) {
				// it's not there. the hunt is on
				$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/models/' . $modelName . 'Model.class.php';
			}
		}

		if(null !== $file && is_readable($file)) {
			require($file);
		}

		if(!class_exists($class)) {
			// it's not there. 
			throw new AgaviAutoloadException(sprintf("Couldn't find class for Model %s", $origModelName));
		}
		
		// so if we're here, we found something, right? good.
		
		$rc = new ReflectionClass($class);
		
		if($rc->implementsInterface('AgaviISingletonModel')) {
			// it's a singleton
			if(!isset($this->singletonModelInstances[$class])) {
				// no instance yet, so we create one
				
				if($parameters === null || $rc->getConstructor() === null) {
					// it has an initialize() method, or no parameters were given, so we don't hand arguments to the constructor
					$this->singletonModelInstances[$class] = new $class();
				} else {
					// we use this approach so we can pass constructor params or if it doesn't have an initialize() method
					$this->singletonModelInstances[$class] = $rc->newInstanceArgs($parameters);
				}
			}
			$model = $this->singletonModelInstances[$class];
		} else {
			// create an instance
			if($parameters === null || $rc->getConstructor() === null) {
				// it has an initialize() method, or no parameters were given, so we don't hand arguments to the constructor
				$model = new $class();
			} else {
				// we use this approach so we can pass constructor params or if it doesn't have an initialize() method
				$model = $rc->newInstanceArgs($parameters);
			}
		}
		
		if(is_callable(array($model, 'initialize'))) {
			// pass the constructor params again. dual use for the win
			$model->initialize($this, (array) $parameters);
		}
		
		return $model;
	}

	/**
	 * Retrieve the name of this Context.
	 *
	 * @return     string A context name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * Retrieve the translation manager.
	 *
	 * @return     AgaviTranslationManager The current TranslationManager
	 *                                     implementation instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTranslationManager()
	{
		return $this->translationManager;
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
}

?>
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
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviContext
{

	protected
		$actionStack      = null,
		$controller       = null,
		$databaseManager  = null,
		$loggerManager    = null,
		$outputType       = null,
		$outputTypes      = array(),
		$request          = null,
		$routing          = null,
		$securityFilter   = null,
		$storage          = null,
		$user             = null,
		$validatorManager = null;
	protected static
		$instances       = null,
		$profiles        = array();

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

	// -------------------------------------------------------------------------
	
	/*
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

	// -------------------------------------------------------------------------
	
	/**
	 * Retrieve the action name for this context.
	 *
	 * @return     string The currently executing action name, if one is set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getActionName ()
	{

		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return $actionEntry->getActionName();

	}

	// -------------------------------------------------------------------------
	
	/**
	 * Retrieve the ActionStack.
	 *
	 * @return     AgaviActionStack the ActionStack instance
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function getActionStack()
	{
		return $this->actionStack;
	}

	/**
	 * Retrieve the controller.
	 *
	 * @return     AgaviController The current Controller implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getController ()
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
	 * @return     mixed A AgaviDatabase instance.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the requested database name does
	 *                                           not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabaseConnection ($name = 'default')
	{

		if ($this->databaseManager != null)
		{

			return $this->databaseManager->getDatabase($name)->getConnection();

		}

		return null;

	}

	/**
	 * Retrieve the database manager.
	 *
	 * @return     AgaviDatabaseManager The current DatabaseManager instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabaseManager ()
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
		} catch (AgaviException $e) {
			$e->printStackTrace();
		} catch (Exception $e) {
			// unknown exception
			$e = new AgaviException($e->getMessage());
			$e->printStackTrace();
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
	 * @param      array overrides, key => class
	 *
	 * @return     AgaviContext instance
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.10.0
	 */
	public function initialize($profile = null, $overrides = array())
	{
		if($profile === null) {
			$profile = AgaviConfig::get('core.default_context', 'stdctx');
		}
		$profile = strtolower($profile);
		
		$this->name = $profile;
		
		static $profiles;
		
		include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/factories.xml', $profile));
		include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/routing.xml', $profile));
		
		return $this;
	}
	
	// We could even add a method to switch contexts on the fly..
	
	/**
	 * Retrieve the module directory for this context.
	 *
	 * @return     string An absolute filesystem path to the directory of the
	 *                    currently executing module if set, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	
	/**
	 * Retrieve the module directory for this context.
	 *
	 * @return     string An absolute filesystem path to the directory of the
	 *                    currently executing module if set, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getModuleDirectory ()
	{

		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return AgaviConfig::get('core.module_dir') . '/' . $actionEntry->getModuleName();

	}

	/**
	 * Retrieve the module name for this context.
	 *
	 * @return     string The currently executing module name, if one is set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getModuleName ()
	{

		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return $actionEntry->getModuleName();

	}

	/**
	 * Retrieve the request.
	 *
	 * @return     Request The current Request implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getRequest ()
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
	public function getSecurityFilter ()
	{

		return $this->securityFilter;

	}

	/**
	 * Retrieve the storage.
	 *
	 * @return     Storage The current Storage implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getStorage ()
	{

		return $this->storage;

	}

	/**
	 * Retrieve the user.
	 *
	 * @return     User The current User implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getUser ()
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
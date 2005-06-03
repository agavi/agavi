<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * Context provides information about the current application context, such as
 * the module and action names and the module directory. 
 * It also serves as a gateway to the core pieces of the framework, allowing
 * objects with access to the context, to access other useful objects such as
 * the current controller, request, user, actionstack, databasemanager, storage,
 * and loggingmanager.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author    Sean Kerr (skerr@mojavi.org) {@link http://www.mojavi.org}
 * @author    Mike Vincent (mike@agavi.org) {@link http://www.agavi.org}
 * @copyright (c) authors 
 * @license		LGPL {@link http://www.agavi.org/LICENSE}
 * @since     0.9.0
 * @version   $Id$
 */
class Context extends AgaviObject
{

	protected
		$actionStack     = null,
		$controller      = null,
		$databaseManager = null,
		$request         = null,
		$securityFilter	 = null,
		$storage         = null,
		$user            = null;
	protected static
		$instance				= null;

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/*
	 * Clone method, overridden to prevent cloning, there can be only one. 
	 *
	 * @author Mike Vincent (mike@agavi.org)	
	 * @since 0.9.0
	 */
	public function __clone()
	{
		trigger_error('Cloning the Context object is not allowed.', E_USER_ERROR);
	}	

	// -------------------------------------------------------------------------
	
	/*
	 * Constuctor method, intentionally made private so the context cannot be created directly
	 *
	 * @author Mike Vincent (mike@agavi.org)	
	 * @since 0.9.0
	 */
	protected function __construct() 
	{
		// Singleton, use Context::getInstance($controller) to get the instance
	}

	// -------------------------------------------------------------------------
	
	/**
	 * Retrieve the action name for this context.
	 *
	 * @return string The currently executing action name, if one is set,
	 *                otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
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
	 * @return ActionStack the ActionStack instance
	 *                
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function getActionStack()
	{
		return $this->actionStack;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the controller.
	 *
	 * @return Controller The current Controller implementation instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getController ()
	{

		return $this->controller;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a database connection from the database manager.
	 *
	 * This is a shortcut to manually getting a connection from an existing
	 * database implementation instance.
	 *
	 * If the AG_USE_DATABASE setting is off, this will return null.
	 *
	 * @param name A database name.
	 *
	 * @return mixed A Database instance.
	 *
	 * @throws <b>DatabaseException</b> If the requested database name does
	 *                                  not exist.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getDatabaseConnection ($name = 'default')
	{

		if ($this->databaseManager != null)
		{

			return $this->databaseManager->getDatabase($name)->getConnection();

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the database manager.
	 *
	 * @return DatabaseManager The current DatabaseManager instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getDatabaseManager ()
	{

		return $this->databaseManager;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the Context instance.
	 *
	 * @param Controller reference to the controller instance.
	 * @return Context instance of the current Context
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public static function getInstance(&$controller)
	{
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class;
		
			if (defined('AG_USE_DATABASE') && AG_USE_DATABASE) {
				self::$instance->databaseManager = new DatabaseManager();
				self::$instance->databaseManager->initialize();
			}
			self::$instance->controller 			= $controller;
			self::$instance->actionStack			= new ActionStack();
		
			require_once(ConfigCache::checkConfig('config/factories.ini'));
		}
		return self::$instance;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the module directory for this context.
	 *
	 * @return string An absolute filesystem path to the directory of the
	 *                currently executing module, if one is set, otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getModuleDirectory ()
	{

		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return AG_MODULE_DIR . '/' . $actionEntry->getModuleName();

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the module name for this context.
	 *
	 * @return string The currently executing module name, if one is set,
	 *                otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getModuleName ()
	{

		// get the last action stack entry
		$actionEntry = $this->actionStack->getLastEntry();

		return $actionEntry->getModuleName();

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the request.
	 *
	 * @return Request The current Request implementation instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getRequest ()
	{

		return $this->request;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the securityFilter
	 *
	 * @return SecurityFilter The current SecurityFilter implementation instance.
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.9.0
	 */
	public function getSecurityFilter ()
	{

		return $this->securityFilter;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the storage.
	 *
	 * @return Storage The current Storage implementation instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getStorage ()
	{

		return $this->storage;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the user.
	 *
	 * @return User The current User implementation instance.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getUser ()
	{

		return $this->user;

	}

}

?>

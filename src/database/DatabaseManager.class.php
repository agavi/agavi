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
 * DatabaseManager allows you to setup your database connectivity before the
 * request is handled. This eliminates the need for a filter to manage database
 * connections.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class DatabaseManager
{

	private
		$databases = array();
	private
		$context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the database connection associated with this Database
	 * implementation.
	 *
	 * @param      string A database name.
	 *
	 * @return     mixed A Database instance.
	 *
	 * @throws     <b>DatabaseException</b> If the requested database name does
	 *                                      not exist.
	 */
	public function getDatabase ($name = 'default')
	{

		if (isset($this->databases[$name]))
		{

			return $this->databases[$name];

		}

		// nonexistent database name
		$error = 'Database "%s" does not exist';
		$error = sprintf($error, $name);

		throw new DatabaseException($error);

	}

	/**
	 * Initialize this DatabaseManager.
	 *
	 * @return     bool true, if initialization completes successfully, 
	 *                  otherwise false.
	 *
	 * @throws     <b>InitializationException</b> If an error occurs while
	 *                                            initializing this 
	 *                                            DatabaseManager.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize($context)
	{

		$this->context = $context;

		// load database configuration
		require_once(ConfigCache::checkConfig('config/databases.ini'));

		return true;

	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @throws     <b>DatabaseException</b> If an error occurs while shutting 
	 *                                      down this DatabaseManager.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{

		// loop through databases and shutdown connections
		foreach ($this->databases as $database)
		{

			$database->shutdown();

		}

	}

}

?>
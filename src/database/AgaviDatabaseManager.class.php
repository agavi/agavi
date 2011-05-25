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
 * AgaviDatabaseManager allows you to setup your database connectivity before 
 * the request is handled. This eliminates the need for a filter to manage 
 * database connections.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviDatabaseManager
{
	/**
	 * @var        string The name of the default database.
	 */
	protected $defaultDatabaseName = null;
	
	/**
	 * @var        array An array of AgaviDatabases.
	 */
	protected $databases = array();

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @return     mixed A AgaviDatabase instance.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the requested database name
	 *                                           does not exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDatabase($name = null)
	{
		if($name === null) {
			$name = $this->defaultDatabaseName;
		}
		
		if(isset($this->databases[$name])) {
			return $this->databases[$name];
		}

		// nonexistent database name
		$error = 'Database "%s" does not exist';
		$error = sprintf($error, $name);
		throw new AgaviDatabaseException($error);
	}
	
	/**
	 * Retrieve the name of the given database instance.
	 *
	 * @param      AgaviDatabase The database to fetch the name of.
	 *
	 * @return     string The name of the database, or false if it was not found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDatabaseName(AgaviDatabase $database)
	{
		return array_search($database, $this->databases, true);
	}

	/**
	 * Returns the name of the default database.
	 *
	 * @return     string The name of the default database.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultDatabaseName()
	{
		return $this->defaultDatabaseName;
	}

	/**
	 * Initialize this DatabaseManager.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this 
	 *                                                 DatabaseManager.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		// load database configuration
		require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/databases.xml'));
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		foreach($this->databases as $database) {
			$database->startup();
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this DatabaseManager.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// loop through databases and shutdown connections
		foreach($this->databases as $database) {
			$database->shutdown();
		}
	}
}

?>
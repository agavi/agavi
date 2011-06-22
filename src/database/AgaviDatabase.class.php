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
 * AgaviDatabase is a base abstraction class that allows you to setup any type
 * of database connection via a configuration file.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviDatabase extends AgaviParameterHolder
{
	/**
	 * @var        AgaviDatabaseManager An AgaviDatabaseManager instance.
	 */
	protected $databaseManager = null;
	
	/**
	 * @var        mixed A database connection.
	 */
	protected $connection = null;

	/**
	 * @var        string The name of the database.
	 */
	private $name = null;

	/**
	 * @var        mixed A database resource.
	 */
	protected $resource = null;

	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract protected function connect();
	
	/**
	 * Retrieve the Database Manager instance for this implementation.
	 *
	 * @return     AgaviDatabaseManager A Database Manager instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDatabaseManager()
	{
		return $this->databaseManager;
	}

	/**
	 * Retrieve the name of this database connection.
	 *
	 * @return     string The name of the database.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Retrieve the database connection associated with this Database
	 * implementation.
	 *
	 * When this is executed on a Database implementation that isn't an
	 * abstraction layer, a copy of the resource will be returned.
	 *
	 * @return     mixed A database connection.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           retrieved.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getConnection()
	{
		if($this->connection === null) {
			$this->connect();
		}

		return $this->connection;
	}

	/**
	 * Retrieve a raw database resource associated with this Database
	 * implementation.
	 *
	 * @return     mixed A database resource.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If no resource could be retrieved
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getResource()
	{
		if($this->resource === null) {
			$this->connect();
		}

		return $this->resource;
	}

	/**
	 * Initialize this Database.
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Database.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		$this->databaseManager = $databaseManager;
		
		$this->setParameters($parameters);
		
		$this->name = $databaseManager->getDatabaseName($this);
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 * It is called during the startup() of the database manager.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract public function shutdown();
}

?>
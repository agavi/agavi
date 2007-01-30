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
 * AgaviDatabaseManager allows you to setup your database connectivity before 
 * the request is handled. This eliminates the need for a filter to manage 
 * database connections.
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
class AgaviDatabaseManager
{
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
	 * @return     mixed A AgaviDatabase instance.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the requested database name
	 *                                           does not exist.
	 */
	public function getDatabase($name = 'default')
	{
		if(isset($this->databases[$name])) {
			return $this->databases[$name];
		}

		// nonexistent database name
		$error = 'Database "%s" does not exist';
		$error = sprintf($error, $name);
		throw new AgaviDatabaseException($error);
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
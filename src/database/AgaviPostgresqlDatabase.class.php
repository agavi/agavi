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
 * AgaviPostgresqlDatabase provides connectivity for the PostgreSQL brand 
 * database.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>database</b>   - [none]      - The database name.
 * # <b>host</b>       - [localhost] - The database host.
 * # <b>method</b>     - [normal]    - How to read connection parameters.
 *                                     Possible values are normal, server, and
 *                                     env. The normal method reads them from
 *                                     the specified values. server reads them
 *                                     from $_SERVER where the keys to retrieve
 *                                     the values are what you specify the value
 *                                     as in the settings. env reads them from
 *                                     $_ENV and works like $_SERVER.
 * # <b>password</b>   - [none]      - The database password.
 * # <b>persistent</b> - [No]        - Indicates that the connection should be
 *                                     persistent.
 * # <b>port</b>       - [none]      - TCP/IP port on which PostgreSQL is
 *                                     listening.
 * # <b>username</b>   - [none]      - The database user.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviPostgresqlDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	protected function connect()
	{
		// determine how to get our parameters
		$method = $this->getParameter('method', 'normal');

		// get parameters
		switch($method) {
			case 'normal':
				// get parameters normally
				$database = $this->getParameter('database');
				$host     = $this->getParameter('host');
				$password = $this->getParameter('password');
				$port     = $this->getParameter('port');
				$user     = $this->getParameter('username');
				// construct connection string
				$string = (($database != null) ? (' dbname='   . $database) : '') .
									(($host != null)     ? (' host='     . $host)     : '') .
									(($password != null) ? (' password=' . $password) : '') .
									(($port != null)     ? (' port='     . $port)     : '') .
									(($user != null)     ? (' user='     . $user)     : '');
				break;

			case 'server':
				// construct a connection string from existing $_SERVER values
				$string = $this->loadParameters($_SERVER);
				break;

			case 'env':
				// construct a connection string from existing $_ENV values
				$string = $this->loadParameters($_ENV);
				break;

			default:
				// who knows what the user wants...
				$error = 'Invalid AgaviPostgreSQLDatabase parameter retrieval method "%s"';
				$error = sprintf($error, $method);
				throw new AgaviDatabaseException($error);
		}

		// let's see if we need a persistent connection
		$persistent = $this->getParameter('persistent', false);

		if($persistent) {
			$this->connection = pg_pconnect($string);
		} else {
			$this->connection = pg_connect($string, PGSQL_CONNECT_FORCE_NEW);
		}

		// make sure the connection went through
		if($this->connection === false) {
			// the connection's foobar'd
			$error = 'Failed to create a AgaviPostgreSQLDatabase connection';

			throw new AgaviDatabaseException($error);
		}

		// since we're not an abstraction layer, we copy the connection
		// to the resource
		$this->resource =& $this->connection;
		
		
		foreach((array)$this->getParameter('init_queries') as $query) {
			pg_query($this->connection, $query);
		}
	}

	/**
	 * Load connection parameters from an existing array.
	 *
	 * @param      array  An array containing the connection information.
	 *
	 * @return     string A connection string.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	protected function loadParameters(array $array)
	{
		$database = $this->getParameter('database');
		$host     = $this->getParameter('host');
		$password = $this->getParameter('password');
		$port     = $this->getParameter('port');
		$user     = $this->getParameter('username');

		// construct connection string
		$string = (($database != null) ? (' dbname='   . $array[$database]) : '') .
							(($host != null)     ? (' host='     . $array[$host])     : '') .
							(($password != null) ? (' password=' . $array[$password]) : '') .
							(($port != null)     ? (' port='     . $array[$port])     : '') .
							(($user != null)     ? (' user='     . $array[$user])     : '');

		return $string;
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
	public function shutdown()
	{
		if($this->connection != null) {
			@pg_close($this->connection);
			$this->connection = $this->resource = null;
		}
	}
}

?>
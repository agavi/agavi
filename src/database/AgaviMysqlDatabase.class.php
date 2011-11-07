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
 * AgaviMysqlDatabase provides connectivity for the MySQL brand database.
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
class AgaviMysqlDatabase extends AgaviDatabase
{
	/**
	 * Initialize this Database.
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.5
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		
		if($matches = preg_grep('/^\s*SET\s+NAMES\b/i', (array)$this->getParameter('init_queries'))) {
			throw new AgaviDatabaseException(sprintf('Depending on your MySQL server configuration, it may not be safe to use "SET NAMES" to configure the connection encoding, as the underlying MySQL client library will not be aware of the changed character set. As a result, string escaping may be applied incorrectly, leading to potential attack vectors in combination with certain multi-byte character sets such as GBK or Big5.' . "\n\n" . 'Please remove the "%s" statement from the "init_queries" configuration parameter in databases.xml and use the configuration parameter "charset" instead.' . "\n\n" . 'The associated PHP bug ticket http://bugs.php.net/47802 contains further information (describes PDO, but the basic issue is the same).', $matches[0]));
		}
	}

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
		// determine how to get our
		$method = $this->getParameter('method', 'normal');

		switch($method) {
			case 'normal':
				// get parameters normally
				$database = $this->getParameter('database');
				$host     = $this->getParameter('host', 'localhost');
				$password = $this->getParameter('password');
				$user     = $this->getParameter('username');
				break;

			case 'server':
				// construct a connection string from existing $_SERVER values
				// and extract them to local scope
				$parameters = $this->loadParameters($_SERVER);
				extract($parameters);
				break;

			case 'env':
				// construct a connection string from existing $_ENV values
				// and extract them to local scope
				$parameters = $this->loadParameters($_ENV);
				extract($parameters);
				break;

			default:
				// who knows what the user wants...
				$error = 'Invalid AgaviMySQLDatabase parameter retrieval method ' .
						 '"%s"';
				$error = sprintf($error, $method);
				throw new AgaviDatabaseException($error);
		}

		// let's see if we need a persistent connection
		$persistent = $this->getParameter('persistent', false);
		
		if($password === null) {
			if($user === null) {
				$args = array($host, null, null);
			} else {
				$args = array($host, $user, null);
			}
		} else {
			$args = array($host, $user, $password);
		}
		
		if($persistent) {
			$this->connection = call_user_func_array('mysql_pconnect', $args);
		} else {
			$this->connection = call_user_func_array('mysql_connect', $args + array(true));
		}
		
		// make sure the connection went through
		if($this->connection === false) {
			// the connection's foobar'd
			$error = 'Failed to create a AgaviMySQLDatabase connection';
			throw new AgaviDatabaseException($error);
		}

		if($this->hasParameter('charset')) {
			if(!mysql_set_charset($this->getParameter('charset'), $this->connection)) {
				$error = 'Failed to set charset "%s"';
				$error = sprintf($error, $this->getParameter('charset'));
				throw new AgaviDatabaseException($error);
			}
		}

		// select our database
		if($database !== null && !@mysql_select_db($database, $this->connection)) {
			// can't select the database
			$error = 'Failed to select AgaviMySQLDatabase "%s"';
			$error = sprintf($error, $database);
			throw new AgaviDatabaseException($error);
		}

		// since we're not an abstraction layer, we copy the connection
		// to the resource
		$this->resource =& $this->connection;
		
		foreach((array)$this->getParameter('init_queries') as $query) {
			mysql_query($query, $this->connection);
		}
	}

	/**
	 * Load connection parameters from an existing array.
	 *
	 * @param      array An array containing the connection information.
	 *
	 * @return     array An associative array of connection parameters.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	protected function loadParameters(array $array)
	{
		// list of available parameters
		$available = array('database', 'host', 'password', 'username');

		$parameters = array();

		foreach($available as $parameter) {
			$$parameter = $this->getParameter($parameter);
			$parameters[$parameter] = ($$parameter != null) ? $array[$$parameter] : null;
		}

		return $parameters;
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
			@mysql_close($this->connection);
			$this->connection = $this->resource = null;
		}
	}
}

?>
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
 * AgaviMysqliDatabase provides advanced connectivity for the MySQL database.
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
 * # <b>username</b>   - [none]      - The database user.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Blake Matheny <bmatheny@compendiumblogware.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviMysqliDatabase extends AgaviMysqlDatabase
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
			throw new AgaviDatabaseException(sprintf('Depending on your MySQL server configuration, it may not be safe to use "SET NAMES" to configure the connection encoding, as the underlying MySQL client library will not be aware of the changed character set. As a result, string escaping may be applied incorrectly (even for prepared statements), leading to potential attack vectors in combination with certain multi-byte character sets such as GBK or Big5.' . "\n\n" . 'Please remove the "%s" statement from the "init_queries" configuration parameter in databases.xml and use the configuration parameter "charset" instead.' . "\n\n" . 'The associated PHP bug ticket http://bugs.php.net/47802 contains further information (describes PDO, but the basic issue is the same).', $matches[0]));
		}
	}

	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Blake Matheny <bmatheny@compendiumblogware.com>
	 * @since      1.0.0
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
				$error = 'Invalid AgaviMySQLiDatabase parameter retrieval method ' .
						 '"%s"';
				$error = sprintf($error, $method);
				throw new AgaviDatabaseException($error);
		}

		if($password === null) {
			if($user === null) {
				$args = array($host, null, null);
			} else {
				$args = array($host, $user, null);
			}
		} else {
			$args = array($host, $user, $password);
		}
		
		$this->connection = new mysqli($args[0], $args[1], $args[2]);
		
		// make sure the connection went through
		if($this->connection === false) {
			// the connection's foobar'd
			$error = 'Failed to create a AgaviMySQLiDatabase connection';
			throw new AgaviDatabaseException($error);
		}
		
		if($this->hasParameter('charset')) {
			if(!$this->connection->set_charset($this->getParameter('charset'))) {
				$error = 'Failed to set charset "%s"';
				$error = sprintf($error, $this->getParameter('charset'));
				throw new AgaviDatabaseException($error);
			}
		}

		// select our database
		if($database !== null && !$this->connection->select_db($database)) {
			// can't select the database
			$error = 'Failed to select AgaviMySQLiDatabase "%s"';
			$error = sprintf($error, $database);
			throw new AgaviDatabaseException($error);
		}

		// since we're not an abstraction layer, we copy the connection
		// to the resource
		$this->resource =& $this->connection;
		
		foreach((array)$this->getParameter('init_queries') as $query) {
			$this->connection->query($query);
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Blake Matheny <bmatheny@compendiumblogware.com>
	 * @since      1.0.0
	 */
	public function shutdown()
	{
		if($this->connection != null) {
			$this->connection->close();
			$this->connection = $this->resource = null;
		}
	}
}

?>
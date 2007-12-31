<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviAdodbDatabase provides connectivity for the AdoDB database abstraction
 * layer.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>classpath</b>      - [none]   - An absolute filesystem path to the main
 *                                      AdoDB class file.
 * # <b>database</b>       - [none]   - The database name.
 * # <b>dsn</b>            - [none]   - The DSN formatted connection string.
 * # <b>host</b>       	   - [none]   - The database host specifications.
 * # <b>method</b>         - [normal] - How to read connection parameters.
 *                                      Possible values are dsn, normal,
 *                                      server, and env. The dsn method reads
 *                                      them from the dsn parameter. The
 *                                      normal method reads them from the
 *                                      specified values. server reads the dsn
 *                                      string from $_SERVER['dsn'], env from 
 *                                      $_ENV['dsn'] (works like $_SERVER).
 * # <b>username</b>   	   - [none]   - The database user.
 * # <b>password</b>       - [none]   - The database password.
 * # <b>persistent</b>     - [No]     - Indicates that the connection should
 *                                      persistent.
 * # <b>dbtype</b>         - [none]   - The type of database (mysql, pgsql,
 *                                      etc).
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Steven Weiss <info@code-factory.de>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @deprecated To be removed in Agavi 1.1
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviAdodbDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If no connection could be created
	 *
	 * @author     Steven Weiss <info@code-factory.de>
	 * @since      0.10.0
	 */
	public function connect()
	{
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');

			switch($method) {

				case 'normal':
					// get parameters normally, and all are required
					$database = $this->getParameter('database', null);
					$host 	  = $this->getParameter('host', null);
					$password = $this->getParameter('password', null);
					$dbtype   = $this->getParameter('dbtype', null);
					$username = $this->getParameter('username', '');
					break;

				case 'dsn':
					$dsn = $this->getParameter('dsn');
					if($dsn == null) {
						// missing required dsn parameter
						$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
						throw new AgaviDatabaseException($error);
					}
					break;

				case 'server':
					// construct a DSN connection string from existing $_SERVER values
					$dsn = $this->loadDSN($_SERVER, $method);
					break;

				case 'env':
					// construct a DSN connection string from existing $_ENV values
					$dsn = $this->loadDSN($_ENV, $method);
					break;

				default:
					// who knows what the user wants...
					$error = 'Invalid AdoDBDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new AgaviDatabaseException($error);

			}

			// get adoDB class path & include the required files (we use exceptions)
			$classPath = $this->getParameter('classpath', 'adodb');

			$error_reporting = error_reporting(error_reporting() & ~E_STRICT);
			require_once($classPath . "/adodb-exceptions.inc.php");
			require_once($classPath . "/adodb.inc.php");
			$error_reporting = error_reporting($error_reporting);

			// connect to our lovely database ;-)
			if(isset($dsn) && $dsn != "") {
				$this->connection = &ADONewConnection($dsn);
			}
			else  {
				// set our flags
				$persistent = $this->getParameter('persistent', false);
					
				$this->connection = &ADONewConnection($dbtype);
				if($persistent) {
					$this->connection->PConnect($host, $username, $password, $database);
				} else {
					$this->connection->NConnect($host, $username, $password, $database);
				}
			}

			// set default fetch mode to 'assoc'
			$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;

			// NOTE:
			// note that my AdoDB-version was build with PHP4 and _connectionID is documented as private. 
			// since there is no function to retrieve this connection id we have to access this member variable
			// directly! so beware that this will break when AdoDB is ported to PHP5 and _connectionID will be
			// declared as private. hopefully the developers will add an accessor function :-/
			$this->resource = $this->connection->_connectionID;
		} catch(ADODB_Exception $e) {
			// the connection's foobar'd
			throw new AgaviDatabaseException($e->getMessage());
		}
	}

	/**
	 * Load a DSN connection string from an existing array.
	 *
	 * @param      array  An array with the connection array at the 'dsn' index.
	 * @param      string The connection parameter read method.
	 *
	 * @return     array An associative array of connection parameters.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the DSN string is not correct.
	 * 
	 * @author     Steven Weiss <info@code-factory.de>
	 * @since      0.10.0
	 */
	private function loadDSN(array $array, $method)
	{
		if(!isset($array['dsn']) || !is_string($array['dsn']) || $array['dsn'] == '') {
			throw new AgaviDatabaseException('Database configuration specifies method "' . $method . '", but is missing dsn parameter');
		}
		return $array['dsn'];
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Steven Weiss <info@code-factory.de>
	 * @since      0.10.0
	 */
	public function shutdown()
	{
		if($this->connection !== null) {
			@$this->connection->Close();
		}
	}
}

?>
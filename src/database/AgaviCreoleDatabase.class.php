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
 * AgaviCreoleDatabase provides connectivity for the Creole database abstraction
 * layer.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>classpath</b>      - [none]   - An absolute filesystem path to the main
 *                                      Creole class file.
 * # <b>database</b>       - [none]   - The database name.
 * # <b>dsn</b>            - [none]   - The DSN formatted connection string.
 * # <b>hostspec</b>       - [none]   - The database host specifications.
 * # <b>method</b>         - [normal] - How to read connection parameters.
 *                                      Possible values are dsn, normal,
 *                                      server, and env. The dsn method reads
 *                                      them from the dsn parameter. The
 *                                      normal method reads them from the
 *                                      specified values. server reads them
 *                                      from $_SERVER where the keys to
 *                                      retrieve the values are what you
 *                                      specify the value as in the settings.
 *                                      env reads them from $_ENV and works
 *                                      like $_SERVER.
 * # <b>compat_assoc_lower</b> - [Off] - Always lowercase the indexes of assoc 
 *                                      arrays
 * # <b>compat_rtrim_string</b> - [Off] - Trim whitespace from end of string 
 *                                        column types
 * # <b>password</b>       - [none]   - The database password.
 * # <b>persistent</b>     - [No]     - Indicates that the connection should
 *                                      persistent.
 * # <b>phptype</b>        - [none]   - The type of database (mysql, pgsql,
 *                                      etc).
 * # <b>username</b>       - [none]   - The database user.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @deprecated To be removed in Agavi 1.1 
 * 
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviCreoleDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	protected function connect()
	{
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');

			switch($method) {
				case 'normal':
					// get parameters normally
					// all params, because we can't know all names!
					$dsn = $this->getParameters();
					// remove our own
					unset($dsn['method']);
					unset($dsn['classpath']);
					unset($dsn['compat_assoc_lower']);
					unset($dsn['compat_rtrim_string']);
					unset($dsn['persistent']);
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
					// construct a DSN connection string from existing $_SERVER
					// values
					$dsn = $this->loadDSN($_SERVER);
					break;

				case 'env':
					// construct a DSN connection string from existing $_ENV
					// values
					$dsn = $this->loadDSN($_ENV);
					break;

				default:
					// who knows what the user wants...
					$error = 'Invalid CreoleDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new AgaviDatabaseException($error);
			}

			// get creole class path
			$classPath = $this->getParameter('classpath');

			// include the creole file
			if($classPath == null) {
				require_once('creole/Creole.php');
			} else {
				require_once($classPath);
			}

			// set our flags
			$compatAssocLower  = $this->getParameter('compat_assoc_lower', false);
			$compatRtrimString = $this->getParameter('compat_rtrim_string', false);
			$persistent        = $this->getParameter('persistent', false);

			$flags  = 0;
			$flags |= ($compatAssocLower)  ? Creole::COMPAT_ASSOC_LOWER  : 0;
			$flags |= ($compatRtrimString) ? Creole::COMPAT_RTRIM_STRING : 0;
			$flags |= ($persistent)        ? Creole::PERSISTENT          : 0;

			// do the duuuurtay work, right thurr
			if($flags > 0) {
				$this->connection = Creole::getConnection($dsn, $flags);
			} else {
				$this->connection = Creole::getConnection($dsn);
			}

			// get our resource
			$this->resource = $this->connection->getResource();
			
			foreach((array)$this->getParameter('init_queries') as $query) {
				$this->connection->executeUpdate($query);
			}
			
		} catch(SQLException $e) {
			// the connection's foobar'd
			throw new AgaviDatabaseException($e->toString());
		}
	}

	/**
	 * Load a DSN connection string from an existing array.
	 *
	 * @param      array  An array containing the connection information.
	 *
	 * @return     array An associative array of connection parameters.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	protected function loadDSN(array $array)
	{
		// determine if a dsn is set, otherwise use separate parameters
		$dsn = $this->getParameter('dsn');

		if($dsn == null) {

			// list of available parameters
			$available = array('database', 'hostspec', 'password', 'phptype', 'username');

			$dsn = array();

			// yes, i know variable variables are ugly, but let's avoid using
			// an array for array's sake in this single spot in the source
			foreach($available as $parameter) {
				$$parameter = $this->getParameter($parameter);
				$dsn[$parameter] = ($$parameter != null) ? $array[$$parameter] : null;
			}

		} else {
			$dsn = $array[$dsn];
		}

		return $dsn;
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
		if($this->connection !== null) {
			@$this->connection->close();
			$this->connection = $this->resource = null;
		}
	}
}

?>
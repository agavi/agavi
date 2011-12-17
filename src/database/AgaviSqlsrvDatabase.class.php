<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviSqlsrvDatabase provides connectivity for the Microsoft SQL Server driver
 * for PHP.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class AgaviSqlsrvDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	protected function connect()
	{
		$serverName = $this->getParameter('server_name');
		if($serverName == null) {
			// missing required server_name parameter
			$error = 'Database configuration is missing "server_name" parameter';
			throw new AgaviDatabaseException($error);
		}

		$settings = array();
		if($this->hasParameter('settings')) {
			foreach((array)$this->getParameter('settings') as $key => $value) {
				if(!sqlsrv_configure($key, is_string($value) && strpos($value, 'SQLSRV_') === 0 && defined($value) ? constant($value) : (is_numeric($value) ? (int)$value : $value))) {
					throw new AgaviDatabaseException(sprintf('Unsupported key or value for setting "%s".', $key));
				}
			}
		}

		$connectionInfo = $this->getParameter('connection_info');
		foreach($connectionInfo as $key => &$value) {
			$value = is_string($value) && strpos($value, 'SQLSRV_') === 0 && defined($value) ? constant($value) : (is_numeric($value) ? (int)$value : $value);
		}
		
		$this->connection = sqlsrv_connect($serverName, $connectionInfo);
		if(!$this->connection) {
			$this->connection = null;
			throw new AgaviDatabaseException(sprintf("%s\n\n%s", sprintf('Could not open database connection "%s".', $this->getName()), implode("\n", $this->getErrors())));
		}
		$this->resource =& $this->connection;

		foreach((array)$this->getParameter('init_queries') as $query) {
			sqlsrv_query($this->connection, $query);
		}
	}
	
	/**
	 * Retrieve an array of formatted and UTF-8 encoded error messages.
	 *
	 * @return     array An array of error strings in UTF-8 encoding.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function getErrors()
	{
		$errors = (array)sqlsrv_errors();
		
		foreach($errors as &$error) {
			if(strtolower($this->getParameter('connection_info[CharacterSet]')) != 'utf-8' || version_compare(phpversion('sqlsrv'), '2', 'lt')) {
				// even when UTF-8 is specified as the encoding for the connection, error messages will be returned in the local codepage in ext/sqlsrv 1.x
				// (not just for connection failures, but also for failed queries etc)
				// also, we need to convert the encoding for newer versions as well if the encoding on the connection was not UTF-8
				$error['message'] = utf8_encode($error['message']);
			}
			$error = sprintf('SQLSTATE %s (code %d): %s', $error['SQLSTATE'], $error['code'], $error['message']);
		}
		
		return $errors;
	}
  
	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function shutdown()
	{
		if($this->connection) {
			sqlsrv_close($this->connection);
			$this->connection = $this->resource = null;
		}
	}
}

?>
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
 * Provides support for session storage using a MySQL brand database.
 *
 * <b>Required parameters:</b>
 *
 * # <b>db_table</b> - [none] - The database table in which session data will be
 *                              stored.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>database</b>     - [default]   - The database connection to use
 *                                       (see databases.xml).
 * # <b>db_id_col</b>    - [sess_id]   - The database column in which the
 *                                       session id will be stored.
 * # <b>db_data_col</b>  - [sess_data] - The database column in which the
 *                                       session data will be stored.
 * # <b>db_time_col</b>  - [sess_time] - The database column in which the
 *                                       session timestamp will be stored.
 * # <b>date_format</b>  - [U]         - The format string passed to date() to
 *                                       format timestamps. Defaults to "U",
 *                                       which means a Unix Timestamp again.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviMysqlSessionStorage extends AgaviSessionStorage
{
	/**
	 * @var        resource A mysql database resource.
	 */
	protected $resource = null;

	/**
	 * Initialize this Storage.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// initialize the parent
		parent::initialize($context, $parameters);

		if(!$this->hasParameter('db_table')) {
			// missing required 'db_table' parameter
			$error = 'Factory configuration file is missing required "db_table" parameter for the Storage category';
			throw new AgaviInitializationException($error);
		}

		// use this object as the session handler
		session_set_save_handler(
			array($this, 'sessionOpen'),
			array($this, 'sessionClose'),
			array($this, 'sessionRead'),
			array($this, 'sessionWrite'),
			array($this, 'sessionDestroy'),
			array($this, 'sessionGC')
		);
	}

	/**
	 * Close a session.
	 *
	 * @return     bool true, if the session was closed, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionClose()
	{
		if($this->resource) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Destroy a session.
	 *
	 * @param      string A session ID.
	 *
	 * @return     bool true, if the session was destroyed, otherwise an
	 *                  exception is thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the session cannot be 
	 *                                           destroyed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionDestroy($id)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');

		// cleanup the session id, just in case
		$id = mysql_real_escape_string($id, $this->resource);

		// delete the record associated with this id
		$sql = sprintf("DELETE FROM %s WHERE %s = '%s'", $db_table, $db_id_col, $id);

		if(@mysql_query($sql, $this->resource)) {
			return true;
		}

		// failed to destroy session
		$error = 'MySQLSessionStorage cannot destroy session id "%s", error reported by server: "%s"';
		$error = sprintf($error, $id, mysql_error($this->resource));
		throw new AgaviDatabaseException($error);
	}

	/**
	 * Cleanup old sessions.
	 *
	 * @param      int The lifetime of a session.
	 *
	 * @return     bool true, if old sessions have been cleaned, otherwise an
	 *                  exception is thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If old sessions cannot be 
	 *                                           cleaned.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionGC($lifetime)
	{
		if(!$this->resource) {
			return false;
		}
		
		// determine deletable session time
		$time = time() - $lifetime;
		$time = date($this->getParameter('date_format', 'U'), $time);

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		if(is_numeric($time)) {
			$time = (int)$time;
		} else {
			$time = "'" . mysql_real_escape_string($time, $this->resource) . "'";
		}

		// delete the records that are expired
		$sql = sprintf('DELETE FROM %s WHERE %s < %s', $db_table, $db_time_col, $time);

		if(@mysql_query($sql, $this->resource)) {
			return true;
		}

		// failed to cleanup old sessions
		$error = 'MySQLSessionStorage cannot delete old sessions, error reported by server: "%s"';
		$error = sprintf($error, mysql_error($this->resource));
		throw new AgaviDatabaseException($error);
	}

	/**
	 * Open a session.
	 *
	 * @param      string The path is ignored.
	 * @param      string The name is ignored.
	 *
	 * @return     bool true, if the session was opened, otherwise an exception is
	 *              thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection with the database
	 *                                           does not exist or cannot be
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', null);

		// get the database resource
		$this->resource = $this->getContext()->getDatabaseManager()->getDatabase($database)->getResource();

		return true;
	}

	/**
	 * Read a session.
	 *
	 * @param      string A session ID.
	 *
	 * @return     bool true, if the session was read, otherwise an exception is
	 *                  thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the session cannot be read.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionRead($id)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// cleanup the session id, just in case
		$id = mysql_real_escape_string($id, $this->resource);

		// delete the record associated with this id
		$sql = sprintf("SELECT %s FROM %s WHERE %s = '%s'", $db_data_col, $db_table, $db_id_col, $id);

		$result = @mysql_query($sql, $this->resource);

		if($result != false && mysql_num_rows($result) > 0) {
			// found the session
			$data = mysql_fetch_row($result);

			return $data[0];
		} elseif($result !== false) {
			return '';
		}

		// failed to read session data
		$error = 'MySQLSessionStorage cannot read session data for id "%s", error reported by server: "%s"';
		$error = sprintf($error, $id, mysql_error($this->resource));
		throw new AgaviDatabaseException($error);
	}

	/**
	 * Write session data.
	 *
	 * @param      string A session ID.
	 * @param      string A serialized chunk of session data.
	 *
	 * @return     bool true, if the session was written, otherwise an exception
	 *                  is thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If the session data cannot be
	 *                                           written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function sessionWrite($id, &$data)
	{
		if(!$this->resource) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// cleanup the session id and data, just in case
		$id   = mysql_real_escape_string($id, $this->resource);
		$data = mysql_real_escape_string($data, $this->resource);

		$ts = date($this->getParameter('date_format', 'U'));
		if(is_numeric($ts)) {
			$ts = (int)$ts;
		} else {
			$ts = "'" . mysql_real_escape_string($ts) . "'";
		}
		// insert or update the record associated with this id
		$sql = sprintf(
			"INSERT INTO %s (%s, %s, %s) VALUES ('%s', '%s', %s) ON DUPLICATE KEY UPDATE %s = VALUES(%s), %s = VALUES(%s)",
			$db_table,
			$db_id_col,
			$db_data_col,
			$db_time_col,
			$id,
			$data,
			$ts,
			$db_data_col,
			$db_data_col,
			$db_time_col,
			$db_time_col
		);

		$result = @mysql_query($sql, $this->resource);
		if($result !== false) {
			return true;
		} else {
			// something went wrong
			$error = 'MySQLSessionStorage cannot insert or update session "%s", error reported by server: "%s"';
			$error = sprintf($error, $id, mysql_error($this->resource));
			throw new AgaviDatabaseException($error);
		}
	}
}

?>
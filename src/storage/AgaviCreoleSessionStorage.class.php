<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * Provides support for session storage using a CreoleDb database abstraction
 * layer.
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
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviCreoleSessionStorage extends AgaviSessionStorage
{
	/**
	 * @var        Connection A Creole Database Connection
	 */
	protected $db;

	/**
	 * Initialize this Storage.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
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
	* @since      0.10.0
	*/
	public function sessionClose()
	{
		if($this->db) {
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function sessionDestroy($id)
	{
		if(!$this->db) {
			return false;
		}
		
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');

		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s WHERE %s = ?', $db_table, $db_id_col);

		try {
			$stmt = $this->db->prepareStatement($sql);
			$stmt->setString(1, $id);
			$stmt->executeUpdate();
		} catch(SQLException $e) {
			$error = 'Creole SQLException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error, 0, $e);
		}
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function sessionGC($lifetime)
	{
		if(!$this->db) {
			return false;
		}
		
		// determine deletable session time
		$time = time() - $lifetime;
		$time = date($this->getParameter('date_format', 'U'), $time);

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s WHERE %s < ?', $db_table, $db_time_col);

		try {
			$stmt = $this->db->prepareStatement($sql);
			
			if(is_numeric($time)) {
				$stmt->setInt(1, (int)$time);
			} else {
				$stmt->setString(1, $time);
			}
			
			$stmt->executeUpdate();
			return true;
		} catch(SQLException $e) {
			$error = 'Creole SQLException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error, 0, $e);
		}
	}

	/**
	 * Open a session.
	 *
	 * @param      string The path is ignored.
	 * @param      string The name is ignored.
	 *
	 * @return     bool true, if the session was opened, otherwise an exception
	 *                  is thrown.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection with the database
	 *                                           does not exist or cannot be
	 *                                           created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', null);

		$this->db = $this->getContext()->getDatabaseConnection($database);
		if($this->db === null || !$this->db instanceof Connection) {
			$error = 'Creole database connection doesn\'t exist. Unable to open session.';
			throw new AgaviDatabaseException($error);
		}

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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function sessionRead($id)
	{
		if(!$this->db) {
			return false;
		}
		
		// get table/columns
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		try {
			$sql = sprintf('SELECT %s FROM %s WHERE %s = ?', $db_data_col, $db_table, $db_id_col);

			$stmt = $this->db->prepareStatement($sql);
			$stmt->setString(1, $id);

			$dbRes = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);

			if($dbRes->next()) {
				$data = $dbRes->getString(1);
				return $data;
			} else {
				return '';
			}
		} catch(SQLException $e) {
			$error = 'Creole SQLException was thrown when trying to read session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error, 0, $e);
		}
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
	 * @throws     <b>AgaviDatabaseException</b> If session data cannot be 
	 *                                           written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function sessionWrite($id, $data)
	{
		if(!$this->db) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		$sql = sprintf('UPDATE %s SET %s = ?, %s = ? WHERE %s = ?', $db_table, $db_data_col, $db_time_col, $db_id_col);

		$ts = date($this->getParameter('date_format', 'U'));

		try {
			$stmt = $this->db->prepareStatement($sql);
			$stmt->setString(1, $data);
			if(is_numeric($ts)) {
				$stmt->setInt(2, (int)$ts);
			} else {
				$stmt->setString(2, $ts);
			}
			$stmt->setString(3, $id);
			$count = $stmt->executeUpdate();
		} catch(SQLException $e) {
			$error = 'Creole SQLException was thrown when trying to update session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error, 0, $e);
		}
		
		if($count == 0) {
			// session does not exist, create it
			$sql = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (?,?,?)', $db_table, $db_id_col, $db_data_col, $db_time_col);

			try {
				$stmt = $this->db->prepareStatement($sql);
				$stmt->setString(1, $id);
				$stmt->setString(2, $data);
				if(is_numeric($ts)) {
					$stmt->setInt(3, (int)$ts);
				} else {
					$stmt->setString(3, $ts);
				}
				$stmt->executeUpdate();
				return true;
			} catch(SQLException $e) {
				$error = 'Creole SQLException was thrown when trying to create session data. Message: ' . $e->getMessage();
				throw new AgaviDatabaseException($error, 0, $e);
			}
		}
	}
}

?>
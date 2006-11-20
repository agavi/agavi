<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * Provides support for session storage using a PDO database abstraction
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
 *                                       (see databases.ini).
 * # <b>db_id_col</b>    - [sess_id]   - The database column in which the
 *                                       session id will be stored.
 * # <b>db_data_col</b>  - [sess_data] - The database column in which the
 *                                       session data will be stored.
 * # <b>db_time_col</b>  - [sess_time] - The database column in which the
 *                                       session timestamp will be stored.
 * # <b>session_name</b> - [Agavi]     - The name of the session.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Makinen <mail@veikkomakinen.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviPdoSessionStorage extends AgaviSessionStorage
{
	/**
	 * @var        mixed PDO Database Connection.
	 */
	protected $connection;

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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionClose()
	{
		// do nothing
		return true;
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionDestroy($id)
	{
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');


		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s WHERE %s = ?', $db_table, $db_id_col);

		try {
			$stmt = $this->connection->prepare($sql);
			$stmt->execute(array($id));
		} catch(PDOException $e) {
			$error = 'PDOException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error);
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionGC($lifetime)
	{
		// determine deletable session time
		$time = time() - $lifetime;

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s  WHERE %s < %d', $db_table, $db_time_col, $time);

		try {
			$this->connection->exec($sql);
			return true;
		} catch(PDOException $e) {
			$error = 'PDOException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error);
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', 'default');

		$this->connection = $this->getContext()->getDatabaseConnection($database);
		if($this->connection === null || !$this->connection instanceof PDO) {
			$error = 'Database connection "' . $database . '" could not be found or is not a PDO database connection.';
			throw new AgaviDatabaseException($error);
		}

		//force clean up before starting session
		$cookieParams = session_get_cookie_params();
		if($cookieParams['lifetime'] > 0) {
			$this->sessionGC($cookieParams['lifetime']);
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionRead($id)
	{
		// get table/columns
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		try {
			$sql = sprintf('SELECT %s FROM %s WHERE %s = ?', $db_data_col, $db_table, $db_id_col);

			$stmt = $this->connection->prepare($sql);
			$stmt->execute(array($id));
			if($result = $stmt->fetch(PDO::FETCH_NUM)) {
				$result = $result[0];
				// pdo is returning the LOB as stream, so check if we had a lob (this seems to differ from db to db)
				if(is_resource($result)) {
					$result = stream_get_contents($result);
				}
				return $result;
			}

			// session does not exist, create it
			$sql = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (?,?,?)', $db_table, $db_id_col, $db_data_col, $db_time_col);

			$stmt = $this->connection->prepare($sql);
			$stmt->execute(array($id, '', time()));
			return '';
		} catch(PDOException $e) {
			$error = 'PDOException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error);
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionWrite($id, $data)
	{
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');
		
		$isOracle = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) == 'oracle';

		if($isOracle) {
			$sql = sprintf('UPDATE %s SET %s = EMPTY_BLOB(), %s = :time WHERE %s = :id RETURNING %s INTO :data', $db_table, $db_data_col, $db_time_col, $db_id_col, $db_data_col);
			
			$sp = fopen('php://memory', 'r+');
			fwrite($sp, $data);
			rewind($sp);
		} else {
			$sql = sprintf('UPDATE %s SET %s = :data, %s = :time WHERE %s = :id', $db_table, $db_data_col, $db_time_col, $db_id_col);
			
			$sp = $data;
		}

		try {
			$time = time();
			$stmt = $this->connection->prepare($sql);
			$stmt->bindParam(':data', $sp, PDO::PARAM_LOB);
			$stmt->bindParam(':time', $time);
			$stmt->bindParam(':id', $id);
			$this->connection->beginTransaction();
			$stmt->execute();
			$this->connection->commit();
			return true;
		} catch(PDOException $e) {
			$this->connection->rollback();
			$error = 'PDOException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error);
		}

		return false;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
		parent::shutdown();
	}
}

?>
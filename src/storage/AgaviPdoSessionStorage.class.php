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
 *                                       (see databases.xml).
 * # <b>db_id_col</b>    - [sess_id]   - The database column in which the
 *                                       session id will be stored.
 * # <b>db_data_col</b>  - [sess_data] - The database column in which the
 *                                       session data will be stored.
 * # <b>db_time_col</b>  - [sess_time] - The database column in which the
 *                                       session timestamp will be stored.
 * # <b>data_as_lob</b>  - [true]      - If true, data is stored as a LOB
 *                                       other wise as a string.
 *                                       (Note: with Oracle LOBs are always
 *                                        used)
 * # <b>date_format</b>  - [U]         - The format string passed to date() to
 *                                       format timestamps. Defaults to "U",
 *                                       which means a Unix Timestamp again.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviPdoSessionStorage extends AgaviSessionStorage
{
	/**
	 * @var        PDO A Database Connection.
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
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
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
		if($this->connection) {
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionDestroy($id)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/column
		$db_table  = $this->getParameter('db_table');
		$db_id_col = $this->getParameter('db_id_col', 'sess_id');

		// delete the record associated with this id
		$sql = sprintf('DELETE FROM %s WHERE %s = ?', $db_table, $db_id_col);

		try {
			$stmt = $this->connection->prepare($sql);
			$result = $stmt->execute(array($id));
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new PDOException($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			return true;
		} catch(PDOException $e) {
			$error = sprintf('PDOException was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionGC($lifetime)
	{
		if(!$this->connection) {
			return false;
		}
		
		// determine deletable session time
		$time = time() - $lifetime;
		$time = date($this->getParameter('date_format', 'U'), $time);

		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		// delete the records that are expired
		$sql = sprintf('DELETE FROM %s WHERE %s < :time', $db_table, $db_time_col);

		try {
			$stmt = $this->connection->prepare($sql);
			if(is_numeric($time)) {
				$time = (int)$time;
				$stmt->bindValue(':time', $time, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':time', $time, PDO::PARAM_STR);
			}
			$result = $stmt->execute();
			
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new PDOException($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			
			return true;
		} catch(PDOException $e) {
			$error = sprintf('PDOException was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionOpen($path, $name)
	{
		// what database are we using?
		$database = $this->getParameter('database', null);

		$this->connection = $this->getContext()->getDatabaseConnection($database);
		if($this->connection === null || !$this->connection instanceof PDO) {
			$error = 'Database connection "' . $database . '" could not be found or is not a PDO database connection.';
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionRead($id)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/columns
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		try {
			$sql = sprintf('SELECT %s FROM %s WHERE %s = ?', $db_data_col, $db_table, $db_id_col);

			$stmt = $this->connection->prepare($sql);
			$result = $stmt->execute(array($id));
			
			if(!$result) {
				$errorInfo = $stmt->errorInfo();
				$e = new PDOException($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			
			if($result = $stmt->fetch(PDO::FETCH_NUM)) {
				$result = $result[0];
				// pdo is returning the LOB as stream, so check if we had a lob (this seems to differ from db to db)
				if(is_resource($result)) {
					$result = stream_get_contents($result);
				}
				return $result;
			}

			return '';
		} catch(PDOException $e) {
			$error = sprintf('PDOException was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sessionWrite($id, $data)
	{
		if(!$this->connection) {
			return false;
		}
		
		// get table/column
		$db_table    = $this->getParameter('db_table');
		$db_data_col = $this->getParameter('db_data_col', 'sess_data');
		$db_id_col   = $this->getParameter('db_id_col', 'sess_id');
		$db_time_col = $this->getParameter('db_time_col', 'sess_time');

		$isOracle = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) == 'oracle';
		$useLob = $this->getParameter('data_as_lob', true);

		if($isOracle) {
			$sql = sprintf('UPDATE %s SET %s = EMPTY_BLOB(), %s = :time WHERE %s = :id RETURNING %s INTO :data', $db_table, $db_data_col, $db_time_col, $db_id_col, $db_data_col);

			$sp = fopen('php://memory', 'r+');
			fwrite($sp, $data);
			rewind($sp);
		} else {
			$sql = sprintf('UPDATE %s SET %s = :data, %s = :time WHERE %s = :id', $db_table, $db_data_col, $db_time_col, $db_id_col);

			$sp = $data;
		}

		$ts = date($this->getParameter('date_format', 'U'));
		if(is_numeric($ts)) {
			$ts = (int)$ts;
		}

		try {
			$columnType = ($isOracle || $useLob) ? PDO::PARAM_LOB : PDO::PARAM_STR;

			$stmt = $this->connection->prepare($sql);
			$stmt->bindParam(':data', $sp, $columnType);
			if(is_int($ts)) {
				$stmt->bindValue(':time', $ts, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':time', $ts, PDO::PARAM_STR);
			}
			$stmt->bindParam(':id', $id);
			$this->connection->beginTransaction();
			if(!$stmt->execute()) {
				$errorInfo = $stmt->errorInfo();
				$e = new PDOException($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
			if(!$this->connection->commit()) {
				$errorInfo = $stmt->errorInfo();
				$e = new PDOException($errorInfo[2], $errorInfo[0]);
				$e->errorInfo = $errorInfo;
				throw $e;
			}
		} catch(PDOException $e) {
			$this->connection->rollback();
			$error = sprintf('PDOException was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
			throw new AgaviDatabaseException($error, 0, $e);
		}
			
		if(!$stmt->rowCount()) {
			// session does not exist, create it
			$sql = sprintf('INSERT INTO %s (%s, %s, %s) VALUES (:id, :data, :time)', $db_table, $db_id_col, $db_data_col, $db_time_col);

			try {
				$stmt = $this->connection->prepare($sql);
				$stmt->bindParam(':id', $id);
				$stmt->bindParam(':data', $sp, $columnType);
				if(is_int($ts)) {
					$stmt->bindValue(':time', $ts, PDO::PARAM_INT);
				} else {
					$stmt->bindValue(':time', $ts, PDO::PARAM_STR);
				}
				$this->connection->beginTransaction();
				if(!$stmt->execute()) {
					$errorInfo = $stmt->errorInfo();
					$e = new PDOException($errorInfo[2], $errorInfo[0]);
					$e->errorInfo = $errorInfo;
					throw $e;
				}
				if(!$this->connection->commit()) {
					$errorInfo = $stmt->errorInfo();
					$e = new PDOException($errorInfo[2], $errorInfo[0]);
					$e->errorInfo = $errorInfo;
					throw $e;
				}
				return true;
			} catch(PDOException $e) {
				$this->connection->rollback();
				$error = sprintf('PDOException was thrown when trying to manipulate session data. Message: "%s"', $e->getMessage());
				throw new AgaviDatabaseException($error, 0, $e);
			}
		} else {
			return true;
		}
	}
}

?>
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
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviPdoOracleSessionStorage extends AgaviPdoSessionStorage
{
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

		$sql = sprintf('UPDATE %s SET %s = EMPTY_BLOB(), %s = ? WHERE %s = ? RETURNING %s INTO ?', $db_table, $db_data_col, $db_time_col, $db_id_col, $db_data_col);

		try {
			$time = time();
			$stmt = $this->ressource->prepare($sql);

			$sp = fopen('php://memory', 'r+');
			fwrite($sp, $data);
			rewind($sp);

			$stmt->bindParam(1, $time);
			$stmt->bindParam(2, $id);
			$stmt->bindParam(3, $sp, PDO::PARAM_LOB);

			$this->ressource->beginTransaction();
			$stmt->execute();
			$this->ressource->commit();
			return true;
		} catch(PDOException $e) {
			$this->ressource->rollback();
			$error = 'PDOException was thrown when trying to manipulate session data. Message: ' . $e->getMessage();
			throw new AgaviDatabaseException($error);
		}

		return false;
	}

}

?>
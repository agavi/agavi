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
 * AgaviPdoDatabase provides connectivity for the PDO database API layer.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviPdoDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
	 * @since      0.9.0
	 */
	protected function connect()
	{
		// determine how to get our parameters
		$method = $this->getParameter('method', 'dsn');

		// get parameters
		switch($method) {
			case 'dsn' :
				$dsn = $this->getParameter('dsn');
				if($dsn == null) {
					// missing required dsn parameter
					$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
					throw new AgaviDatabaseException($error);
				}
				break;
		}

		try {
			$username = $this->getParameter('username');
			$password = $this->getParameter('password');

			$options = array();

			if($this->hasParameter('options')) {
				foreach((array)$this->getParameter('options') as $key => $value) {
					$options[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}

			$this->connection = new PDO($dsn, $username, $password, $options);

			// default connection attributes
			$attributes = array(
				// lets generate exceptions instead of silent failures
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			if($this->hasParameter('attributes')) {
				foreach((array)$this->getParameter('attributes') as $key => $value) {
					$attributes[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}
			foreach($attributes as $key => $value) {
				$this->connection->setAttribute($key, $value);
			}
			foreach((array)$this->getParameter('init_queries') as $query) {
				$this->connection->exec($query);
			}
		} catch(PDOException $e) {
			throw new AgaviDatabaseException($e->getMessage());
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// assigning null to a previously open connection object causes a disconnect
		$this->connection = null;
	}
}

?>
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
		
		if($this->getParameter('warn_mysql_charset', true) && strpos($this->getParameter('dsn'), 'mysql:') === 0) {
			if($matches = preg_grep('/^\s*SET\s+NAMES\b/i', (array)$this->getParameter('init_queries'))) {
				throw new AgaviDatabaseException(sprintf(
					'Depending on your MySQL server configuration, it may not be safe to use "SET NAMES" to configure the connection encoding, as the underlying MySQL client library will not be aware of the changed character set.' .
					'As a result, string escaping may be applied incorrectly, leading to potential attack vectors in combination with certain multi-byte character sets such as GBK or Big5.' . "\n\n" .
					'Please use the "charset" DSN option instead and remove the "%s" statement from the "init_queries" configuration parameter in databases.xml.' . "\n\n" .
					'The associated PHP bug ticket http://bugs.php.net/47802 contains further information.',
					$matches[0]
				));
			}
			if(strpos($this->getParameter('dsn'), ';charset=') !== false && version_compare(PHP_VERSION, '5.3.6', '<')) {
				throw new AgaviDatabaseException(
					'The "charset" option in a PDO_MYSQL DSN has no effect in PHP versions prior to 5.3.6. In combination with certain multi-byte character sets such as GBK or Big5, this may cause incorrectly escaped characters in prepared statements and quoted strings, potentially leading to vulnerabilities in application code.' . "\n\n" .
					'There are two ways of working around this problem:' . "\n" .
					'1) Upgrade to PHP 5.3.6 or later :)' . "\n" .
					'2) Double-check your my.cnf configuration to make sure the default connection charset is compatible with the charset you wish to set (for example, latin1 as the connection default in combination with "SET NAMES utf8" is safe), then revert to using "SET NAMES" in "init_queries" and set the "warn_mysql_charset" configuration parameter on this connection to false. In this case, it is recommended to use native prepared statements by setting the flag PDO::ATTR_EMULATE_PREPARES to 0 in "options" or "attributes", but be advised that per-statement attributes can override this setting, and calls to PDO::quote() might still yield incorrectly escaped strings.'  . "\n\n" .
					'The associated PHP bug ticket http://bugs.php.net/47802 contains further information.'
				);
			}
		}
	}

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

			$this->connection = $this->resource = new PDO($dsn, $username, $password, $options);

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
		$this->connection = $this->resource = null;
	}
}

?>
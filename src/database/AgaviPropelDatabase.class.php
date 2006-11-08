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
 * An Agavi Database driver for Propel, derived from the native Creole driver. 
 * 
 * <b>Optional parameters:</b>
 *
 * # <b>config</b>         - [none]    - path to the Propel runtime config file
 * # <b>datasource</b>     - [default] - datasource to use for the connection
 * # <b>use_as_default</b> - [false]   - use as default if multiple connections
 *                                       are specified. The configuration file
 *                                       that has been flagged using this param
 *                                       is be used when Propel is initialized
 *                                       via PropelAutoload. By default, the
 *                                       last config file in database.ini will
 *                                       be used.
 * # <b>use_autoload</b>   - [true]    - set this to false if you don't want to
 *                                       use the Propel autoloading feature.
 *                                       Instead, Propel will be initialized 
 *                                       on connect(). This is for 0.9.0 B/C.
 * 
 *
 * @package    agavi
 * @subpackage database
 * 
 * @author     Agavi Project <info@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviPropelDatabase extends AgaviDatabase
{
	/**
	 * Stores the actual AgaviDatabase implementation (AgaviCreoleDatabase or 
	 * AgaviPdoDatabase).
	 *
	 * @var        AgaviDatabase The AgaviDatabase instance used internally.
	 *
	 * @since      0.11.0
	 */
	protected $agaviDatabase = null;
	
	/**
	 * An array of the classes in the runtime configuration classes list.
	 *
	 * @var        array An array of classes and their file names.
	 *
	 * @since      0.11.0
	 */
	protected $propelAutoloads = array();
	
	/**
	 * Stores the path of the configuration file that will be passed to
	 * Propel::init() when using Propel autoloading magic
	 *
	 * @var        string The filesystem path to the default runtime config.
	 *
	 * @since      0.10.0
	 */
	private static $defaultConfigPath = null;

	/**
	 * Stores whether a Propel configuration file path has been explicitly set
	 * as default for use with Propel::init() in database.xml
	 *
	 * @var        bool A flag indicating whether a default config path was set.
	 *
	 * @since      0.10.0
	 */
	private static $defaultConfigPathSet = false;

	/**
	 * Returns the path to the config file that is passed to Propel::init() when
	 * PropelAutoload.php is used in autoload.xml
	 *
	 * @return     mixed The path if one has been set, otherwise null
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public static function getDefaultConfigPath()
	{
		return self::$defaultConfigPath;
	}

	/**
	 * Sets the path to the config file that is passed to Propel::init() when
	 * PropelAutoload.php is used in autoload.xml
	 *
	 * @param      string The path to the configuration file
	 *
	 * @return     mixed The old path if one was set previously, otherwise null
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	protected static function setDefaultConfigPath($path)
	{
		$return = self::getDefaultConfigPath();
		self::$defaultConfigPath = $path;
		return $return;
	}

	/**
	 * Returns whether a Propel configuration file path has been explicitly set
	 * as default for use with Propel::init() in database.xml
	 *
	 * @return     bool true, if a Propel configuration file path has explicitely
	 *                  been set as default in database.ini, otherwise false
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	protected static function isDefaultConfigPathSet()
	{
		return self::$defaultConfigPathSet;
	}

	/**
	 * Sets a flag indicating a Propel configuration file path has been
	 * explicitly set as default for use with Propel::init() in database.xml
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	protected static function setDefaultConfigPathSet()
	{
		self::$defaultConfigPathSet = true;
	}

	/**
	 * Connect to the database.
	 * This method is used only if the <kbd>use_autoload</kbd> parameter is false
	 * 
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Dusty Matthews <dustym@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function connect()
	{
		$useAutoload = $this->getParameter('use_autoload', true);
		if($useAutoload) {
			return $this->agaviDatabase->connect();
		}
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');
			switch ($method) {
				case 'normal':
					$runtime = AgaviConfigHandler::replaceConstants($this->getParameter('config', null));
					break;
				case 'server':
					$runtime = $_SERVER[$this->getParameter('config')];
					break;
				case 'env':
					$runtime = $_ENV[$this->getParameter('config')];
					break;
				default:
					$error = 'Invalid PropelDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new AgaviDatabaseException($error);
			}
			// get propel class path
			$classPath = AgaviConfigHandler::replaceConstants($this->getParameter('classpath',null));
			// set the include path to our Propel generated classes
			if(!is_null($classPath)) {
				set_include_path(get_include_path().PATH_SEPARATOR.$classPath);
			}
			require_once('propel/Propel.php');
			// Everything looks good. Off to the races.
			Propel::init($runtime);
			$this->connection = Propel::getConnection();
			$this->resource =& $this->connection->getResource();
		} catch(PropelException $e) {
			// the connection's foobar'd
			throw new AgaviDatabaseException($e->toString());
		}
	}

	/**
	 * Retrieve the database connection associated with this Database
	 * implementation.
	 *
	 * When this is executed on a Database implementation that isn't an
	 * abstraction layer, a copy of the resource will be returned.
	 *
	 * @return     mixed A database connection.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be
	 *                                           retrieved.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getConnection()
	{
		if($this->connection === null) {
			$this->connection = $this->agaviDatabase->getConnection();
		}

		return parent::getConnection();
	}

	/**
	 * Retrieve a raw database resource associated with this Database
	 * implementation.
	 *
	 * @return     mixed A database resource.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If no resource could be retrieved
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getResource()
	{
		if($this->resource === null) {
			$this->resource = $this->agaviDatabase->getResource();
		}

		return parent::getResource();
	}

	/**
	 * Load Propel config
	 * 
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		$useAutoload = $this->getParameter('use_autoload', true);
		$propel13 = false;
		$configPath = AgaviConfigHandler::replaceConstants($this->getParameter('config'));
		$datasource = $this->getParameter('datasource', null);
		$use_as_default = $this->getParameter('use_as_default', false);
		$config = require($configPath);
		if($datasource === null || $datasource == 'default') {
			$datasource = $config['propel']['datasources']['default'];
		}
		if(isset($config['propel']['version']) && version_compare($config['propel']['version'], '1.3.0-dev') >= 0) {
			// it's Propel 1.3 or later, we wrap a PDO connection.
			$this->agaviDatabase = new AgaviPdoDatabase();
			$this->agaviDatabase->initialize($databaseManager, $parameters);
		} else {
			// Propel 1.1 or 1.2, so let's use Creole for the connection.
			$this->agaviDatabase = new AgaviCreoleDatabase();
			$this->agaviDatabase->initialize($databaseManager, $parameters);
		}
		if($useAutoload) {
			foreach($config['propel']['datasources'][$datasource]['connection'] as $key => $value) {
				$this->agaviDatabase->setParameter($key, $value);
			}
			$this->agaviDatabase->setParameter('method', 'normal');
			if(!self::isDefaultConfigPathSet()) {
				self::setDefaultConfigPath($configPath);
				if($use_as_default) {
					self::setDefaultConfigPathSet();
				}
			}
			if(isset($config['propel']['datasources'][$datasource]['classes'])) {
				$this->propelAutoloads = $config['propel']['datasources'][$datasource]['classes'];
				spl_autoload_register(array($this, 'autoload'));
			}
		}
	}
	
	/**
	 * Autoloading function for Propel 1.3.
	 *
	 * @param      string The name of the class to autoload.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function autoload($className) {
		if(isset($this->propelAutoloads[$className])) {
			// it's a propel runtime class. autoload Propel, it will handle the rest.
			class_exists('Propel');
		}
	}

	/**
	 * Get the path to the Propel config file for this connection which has been
	 * specified in databases.xml.
	 *
	 * @return     string The path to the Propel configuration file
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getConfigPath()
	{
		return $this->getParameter('config');
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
		$this->agaviDatabase->shutdown();
	}
}

?>
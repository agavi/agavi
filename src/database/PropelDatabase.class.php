<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Agavi Foundation                                 |
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
 * @package agavi
 * @subpackage database
 * 
 * @since 0.9.0
 * @author Agavi Foundation (info@agavi.org)
 * @author David Zuelke (dz@bitxtender.com)
 */
class PropelDatabase extends CreoleDatabase
{
	/**
	 * Stores the path of the configuration file that will be passed to
	 * Propel::init() when using Propel autoloading magic
	 * @since 0.10.0
	 */
	static $defaultConfigPath = null;

	/**
	 * Stores whether a Propel configuration file path has been explicitly set
	 * as default for use with Propel::init() in database.ini
	 * @since 0.10.0
	 */
	static $defaultConfigPathSet = false;

	/**
	 * Returns the path to the config file that is passed to Propel::init() when
	 * PropelAutoload.php is used in autoload.ini
	 * @since 0.10.0
	 * @returns mixed The path if one has been set, otherwise null
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public static function getDefaultConfigPath()
	{
		return self::$defaultConfigPath;
	}

	/**
	 * Sets the path to the config file that is passed to Propel::init() when
	 * PropelAutoload.php is used in autoload.ini
	 * @since 0.10.0
	 * @param string The path to the configuration file
	 * @returns mixed The old path if one was set previously, otherwise null
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	protected static function setDefaultConfigPath($path)
	{
		$return = self::getDefaultConfigPath();
		self::$defaultConfigPath = $path;
		return $return;
	}

	/**
	 * Returns whether a Propel configuration file path has been explicitly set
	 * as default for use with Propel::init() in database.ini
	 * @since 0.10.0
	 * @return bool true, if a Propel configuration file path has explicitely
	 *              been set as default in database.ini, otherwise false
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	protected static function isDefaultConfigPathSet()
	{
		return self::$defaultConfigPathSet;
	}

	/**
	 * Sets a flag indicating a Propel configuration file path has been
	 * explicitly set as default for use with Propel::init() in database.ini
	 * @since 0.10.0
	 * @return void
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	protected static function setDefaultConfigPathSet()
	{
		self::$defaultConfigPathSet = true;
	}

	/**
	 * Connect to the database.
	 * This method is used only if param.use_autoload is false
	 * 
	 * @since 0.9.0
	 * @access public
	 * @return void
	 * @throws <b>DatabaseException</b> If a connection could not be created.
	 * @author Dusty Matthews (dustym@agavi.org)
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public function connect ()
	{
		$useAutoload = $this->getParameter('use_autoload', true);
		if($useAutoload)
		{
			return parent::connect();
		}
		try {
			// determine how to get our settings
			$method = $this->getParameter('method', 'normal');
			switch ($method) {
				case 'normal':
					$runtime = ConfigHandler::replaceConstants($this->getParameter('config', null));
					break;
				case 'server':
					$runtime =& $_SERVER[$this->getParameter('config')];
					break;
				case 'env':
					$runtime =& $_ENV[$this->getParameter('config')];
					break;
				default:
					$error = 'Invalid PropelDatabase parameter retrieval method "%s"';
					$error = sprintf($error, $method);
					throw new DatabaseException($error);
			}
			// get propel class path
			$classPath = ConfigHandler::replaceConstants($this->getParameter('classpath',null));
			// set the include path to our Propel generated classes
			if (!is_null($classPath)) {
				set_include_path(get_include_path().PATH_SEPARATOR.$classPath);
			}
			require_once('creole/SQLException.php');
			require_once('propel/Propel.php');
			require_once('propel/util/Criteria.php');
			require_once('propel/map/DatabaseMap.php');
			// Everything looks good. Off to the races.
			Propel::init($runtime);
			$this->connection = Propel::getConnection();
			$this->resource =& $this->connection->getResource();
		} catch (SQLException $e) {
			// the connection's foobar'd
			throw new DatabaseException($e->toString());
		}
	}

	/**
	 * Load Propel config
	 * 
	 * @since 0.10.0
	 * @access puclic
	 * @param array An associative array of initialization parameters.
	 * @return void
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public function initialize($parameters = null)
	{
		parent::initialize($parameters);
		$useAutoload = $this->getParameter('use_autoload', true);
		if($useAutoload)
		{
			$configPath = ConfigHandler::replaceConstants($this->getParameter('config'));
			$datasource = $this->getParameter('datasource', null);
			$use_as_default = $this->getParameter('use_as_default', false);
			$config = require($configPath);
			if($datasource === null || $datasource == 'default')
			{
				$datasource = $config['propel']['datasources']['default'];
			}
			foreach($config['propel']['datasources'][$datasource]['connection'] as $key => $value)
			{
				$this->setParameter($key, $value);
			}
			$this->setParameter('method', 'normal');
			if(!self::isDefaultConfigPathSet())
			{
				self::setDefaultConfigPath($configPath);
				if($use_as_default)
				{
					self::setDefaultConfigPathSet();
				}
			}
		}
	}

	/**
	 * Get the path to the Propel config file for this connection which has been
	 * specified in databases.ini.
	 * @since 0.10.0
	 * @access public
	 * @return string The path to the Propel configuration file
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public function getConfigPath()
	{
		return $this->getParameter('config');
	}
}

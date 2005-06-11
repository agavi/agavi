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
 * @package agavi
 * @subpackage database
 * 
 * @since 1.0 
 * @author Agavi Foundation (info@agavi.org)
 * @author David Zuelke (dz@bitxtender.com)
 */
class PropelDatabase extends CreoleDatabase
{
	/**
	 * Stores the path of the configuration file that will be passed to
	 * Propel::init() when using Propel autoloading magic
	 */
	static $defaultConfigPath = null;

	/**
	 * Stores whether a Propel configuration file path has been explicitly set
	 * as default for use with Propel::init() in database.ini
	 */
	static $defaultConfigPathSet = false;

	/**
	 * Returns the path to the config file that is passed to Propel::init() when
	 * PropelAutoload.php is used in autoload.ini
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
	 * @return void
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	protected static function setDefaultConfigPathSet()
	{
		self::$defaultConfigPathSet = true;
	}

	/**
	 * Load Propel config
	 * 
	 * @access puclic
	 * @param array An associative array of initialization parameters.
	 * @return void
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public function initialize($parameters = null)
	{
		parent::initialize($parameters);
		$configPath = $this->getParameter('config');
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

	/**
	 * Get the path to the Propel config file for this connection which has been
	 * specified in databases.ini.
	 * @access public
	 * @return string The path to the Propel configuration file
	 * @author David Zuelke (dz@bitxtender.com)
	 */
	public function getConfigPath()
	{
		return $this->getParameter('config');
	}
	
	/**
	 * Execute the shutdown procedure. 
	 * 
	 * @since 1.0
	 * @access public
	 * @return void
	 * @throws <b>DatabaseException</b> If an error occurs while shutting down this database.
	 * @author Dusty Matthews (dustym@agavi.org)
	 */
	public function shutdown()
	{
		if ($this->connection !== null) {
			@$this->connection->close();
		}
	}
}
?>

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * ConfigCache allows you to customize the format of a configuration file to
 * make it easy-to-use, yet still provide a PHP formatted result for direct
 * inclusion into your modules.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id$
 */
class ConfigCache extends AgaviObject
{

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+

	private static
		$handlers = array();

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Load a configuration handler.
	 *
	 * @param string The handler to use when parsing a configuration file.
	 * @param string An absolute filesystem path to a configuration file.
	 * @param string An absolute filesystem path to the cache file that will be
	 *               written.
	 *
	 * @return void
	 *
	 * @throws <b>ConfigurationException</b> If a requested configuration file
	 *                                       does not have an associated
	 *                                       configuration handler.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	private static function callHandler ($handler, $config, $cache)
	{

		if (count(self::$handlers) == 0) {
			// we need to load the handlers first
			self::loadConfigHandlers();
		}

		// grab the base name of the handler
		$basename = basename($handler);

		if (isset(self::$handlers[$handler])) {
			// we have a handler associated with the full configuration path
			// call the handler and retrieve the cache data
			$data =& self::$handlers[$handler]->execute($config);
			self::writeCacheFile($config, $cache, $data, false);
			return;
		} else if (isset(self::$handlers[$basename]))	{
			// we have a handler associated with the configuration base name
			// call the handler and retrieve the cache data
			$data =& self::$handlers[$basename]->execute($config);
			self::writeCacheFile($config, $cache, $data, false);
			return;
		} else {
			// let's see if we have any wildcard handlers registered that match
			// this basename
			foreach (self::$handlers as $key => $handlerInstance)	{
				// replace wildcard chars in the configuration
				$pattern = str_replace('.', '\.', $key);
				$pattern = str_replace('*', '.*?', $pattern);
				// create pattern from config
				$pattern = '#' . $pattern . '#';
				if (preg_match($pattern, $handler))	{
					// call the handler and retrieve the cache data
					$data =& self::$handlers[$key]->execute($config);
					self::writeCacheFile($config, $cache, $data, false);
					return;
				}
			}
		}
		// we do not have a registered handler for this file
		$error = 'Configuration file "%s" does not have a registered handler';
		$error = sprintf($error, $config);
		throw new ConfigurationException($error);
	}

	// -------------------------------------------------------------------------

	/**
	 * Check to see if a configuration file has been modified and if so
	 * recompile the cache file associated with it.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Agavi AG_WEBAPP_DIR application setting.
	 *
	 * @param string A filesystem path to a configuration file.
	 *
	 * @return string An absolute filesystem path to the cache filename
	 *                associated with this specified configuration file.
	 *
	 * @throws <b>ConfigurationException</b> If a requested configuration file
	 *                                       does not exist.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public static function checkConfig ($config)
	{
		// the full filename path to the config, which might not be what we were given.
		$filename = Toolkit::isPathAbsolute($config) ? $config : AG_WEBAPP_DIR . '/' . $config;

		if (!is_readable($filename)) {
			throw new ConfigurationException('Configuration file "' . $filename . '" does not exist or is unreadable.');
		}

		// the cache filename we'll be using
		$cache = self::getCacheName($config);

		if (!is_readable($cache) || filemtime($filename) > filemtime($cache))	{
			// configuration file has changed so we need to reparse it
			self::callHandler($config, $filename, $cache);
		}

		return $cache;

	}

	// -------------------------------------------------------------------------

	/**
	 * Clear all configuration cache files.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public static function clear ()
	{

		self::clearCache(AG_CACHE_DIR);

	}

	// -------------------------------------------------------------------------

	/**
	 * Clear all configuration cache files.
	 *
	 * This method exists to prevent accidental deletion of non-cache directory
	 * files.
	 *
	 * @param string An absolute filesystem path to a cache directory.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	private static function clearCache ($directory)
	{

		// open a file point to the cache dir
		$fp = opendir($directory);

		// ignore names
		$ignore = array('.', '..', 'CVS', '.svn');

		while (($file = readdir($fp)) !== false) {
			if (!in_array($file, $ignore)) {
				if (is_dir($file)) {
				    // recurse through directory
				    self::clearCache($file);
				    // delete the directory
				    rmdir($file);
				} else {
				    // delete the file
				    unlink($directory . '/' . $file);
				}

			}

		}

		// close file pointer
		fclose($fp);

	}

	// -------------------------------------------------------------------------

	/**
	 * Convert a normal filename into a cache filename.
	 *
	 * @param string A normal filename.
	 *
	 * @return string An absolute filesystem path to a cache filename.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public static function getCacheName ($config)
	{

		if (strlen($config) > 3 && ctype_alpha($config{0}) &&	$config{1} == ':' && $config{2} == '\\') {
			// file is a windows absolute path, strip off the drive letter
			$config = substr($config, 3);
		}

		// replace unfriendly filename characters with an underscore and postfix the name with a php extension
		$config  = str_replace(array('\\', '/'), '_', $config) . '.php';
		return AG_CACHE_DIR . '/' . $config;

	}

	// -------------------------------------------------------------------------

	/**
	 * Import a configuration file.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Agavi AG_WEBAPP_DIR application setting.
	 *
	 * @param string A filesystem path to a configuration file.
	 * @param bool   Only allow this configuration file to be included once per request?
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public static function import ($config, $once = true)
	{
		$cache = self::checkConfig($config);

		if ($once) {
			include_once($cache);
		} else {
			include($cache);
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Load all configuration application and module level handlers.
	 *
	 * @return void
	 *
	 * @throws <b>ConfigurationException</b> If a configuration related error
	 *                                       occurs.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	private static function loadConfigHandlers ()
	{

		// manually create our config_handlers.ini handler
		self::$handlers['config_handlers.ini'] = new RootConfigHandler();
		self::$handlers['config_handlers.ini']->initialize();

		// application configuration handlers
		require_once(ConfigCache::checkConfig('config/config_handlers.ini'));

		// module level configuration handlers

		// make sure our modules directory exists
		if (is_readable(AG_MODULE_DIR))	{
			// ignore names
			$ignore = array('.', '..', 'CVS', '.svn');

			// create a file pointer to the module dir
			$fp = opendir(AG_MODULE_DIR);

			// loop through the directory and grab the modules
			while (($directory = readdir($fp)) !== false)	{

				if (!in_array($directory, $ignore))
				{

				    $config = AG_MODULE_DIR . '/' . $directory . '/config/config_handlers.ini';

				    if (is_readable($config)) {

						// initialize the root configuration handler with this
						// module name
						$params = array('module_level' => true,
						                'module_name'  => $directory);

						self::$handlers['config_handlers.ini']->initialize($params);

						// replace module dir path with a special keyword that
						// checkConfig knows how to use
						$config = 'modules/' . $directory .
						          '/config/config_handlers.ini';

						require_once(ConfigCache::checkConfig($config));

				    }

				}

			}

			// close file pointer
			fclose($fp);

		} else {

			// module directory doesn't exist or isn't readable
			$error = 'Module directory "%s" does not exist or is not readable';
			$error = sprintf($error, AG_MODULE_DIR);

			throw new ConfigurationException($error);

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Write a cache file.
	 *
	 * @param string An absolute filesystem path to a configuration file.
	 * @param string An absolute filesystem path to the cache file that will
	 *               be written.
	 * @param string Data to be written to the cache file.
	 * @param string Should we append the data?
	 *
	 * @throws <b>CacheException</b> If the cache file cannot be written.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	private static function writeCacheFile ($config, $cache, &$data, $append)
	{

		$flags = ($append) ? FILE_APPEND : 0;

		if (@file_put_contents($cache, $data, $flags) === false)
		{

			// cannot write cache file
			$error = 'Failed to write cache file "%s" generated from ' .
				     'configuration file "%s"';
			$error = sprintf($error, $cache, $config);

			throw new CacheException($error);

		}

	}

}

?>

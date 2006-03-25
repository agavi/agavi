<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviConfigCache allows you to customize the format of a configuration 
 * file to make it easy-to-use, yet still provide a PHP formatted result 
 * for direct inclusion into your modules.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviConfigCache
{
	
	const CACHE_SUBDIR = 'config';

	private static
		$handlers = array();

	/**
	 * Load a configuration handler.
	 *
	 * @param      string The handler to use when parsing a configuration file.
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that 
	 *                    will be written.
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviConfigurationException</b> If a requested configuration 
	 *                                                file does not have an 
	 *                                                associated config handler.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private static function callHandler($handler, $config, $cache, $context)
	{

		if(count(self::$handlers) == 0) {
			// we need to load the handlers first
			self::loadConfigHandlers();
		}

		// grab the base name of the handler
		$basename = basename($handler);

		if(isset(self::$handlers[$handler])) {
			// we have a handler associated with the full configuration path
			// call the handler and retrieve the cache data
			$data = self::$handlers[$handler]->execute($config, $context);
			self::writeCacheFile($config, $cache, $data, false);
			return;
		} elseif(isset(self::$handlers[$basename])) {
			// we have a handler associated with the configuration base name
			// call the handler and retrieve the cache data
			$data = self::$handlers[$basename]->execute($config, $context);
			self::writeCacheFile($config, $cache, $data, false);
			return;
		} else {
			// let's see if we have any wildcard handlers registered that match
			// this basename
			foreach (self::$handlers as $key => $handlerInstance)	{
				// replace wildcard chars in the configuration and create the pattern
				$pattern = sprintf('#%s#', str_replace('\*', '.*?', preg_quote($key)));

				if(preg_match($pattern, $handler)) {
					// call the handler and retrieve the cache data
					$data = $handlerInstance->execute($config, $context);
					self::writeCacheFile($config, $cache, $data, false);
					return;
				}
			}
		}

		// we do not have a registered handler for this file
		$error = 'Configuration file "%s" does not have a registered handler';
		$error = sprintf($error, $config);
		throw new AgaviConfigurationException($error);
	}

	/**
	 * Check to see if a configuration file has been modified and if so
	 * recompile the cache file associated with it.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Agavi "core.webapp_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 *
	 * @return     string An absolute filesystem path to the cache filename
	 *                    associated with this specified configuration file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration file
	 *                                             does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function checkConfig ($config, $context = null)
	{
		// the full filename path to the config, which might not be what we were given.
		$filename = AgaviToolkit::isPathAbsolute($config) ? $config : AgaviConfig::get('core.webapp_dir') . '/' . $config;

		if (!is_readable($filename)) {
			throw new AgaviUnreadableException('Configuration file "' . $filename . '" does not exist or is unreadable.');
		}

		// the cache filename we'll be using
		$cache = self::getCacheName($config, $context);

		if (!is_readable($cache) || filemtime($filename) > filemtime($cache))	{
			// configuration file has changed so we need to reparse it
			self::callHandler($config, $filename, $cache, $context);
		}

		return $cache;

	}

	/**
	 * Clear all configuration cache files.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function clear ()
	{
		AgaviToolkit::clearCache(self::CACHE_SUBDIR);
	}

	/**
	 * Clear all configuration cache files.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	private static function clearCache($directory = '')
	{
		AgaviToolkit::clearCache(self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . $directory);
	}

	/**
	 * Convert a normal filename into a cache filename.
	 *
	 * @param      string A normal filename.
	 *
	 * @return     string An absolute filesystem path to a cache filename.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function getCacheName ($config, $context = null)
	{
		$environment = AgaviConfig::get('core.environment');

		if (strlen($config) > 3 && ctype_alpha($config{0}) &&	$config{1} == ':' && ($config{2} == '\\' || $config{2} == '/')) {
			// file is a windows absolute path, strip off the drive letter
			$config = substr($config, 3);
		}

		// replace unfriendly filename characters with an underscore and postfix the name with a php extension
		$config  = str_replace(array('\\', '/'), '_', $config) . '_' . $environment . '_' . $context . '.php';
		return AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . $config;

	}

	/**
	 * Import a configuration file.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Agavi "core.webapp_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 * @param      bool   Only allow this configuration file to be included once 
	 *                    per request?
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
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

	/**
	 * Load all configuration application and module level handlers.
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviConfigurationException</b> If a configuration related 
	 *                                                error occurs.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private static function loadConfigHandlers ()
	{
		// since we only need the parser and handlers when the config is not cached
		// it is sufficient to include them at this stage 
		require_once(AgaviConfig::get('core.app_dir') . '/config/ConfigHandlersConfigHandler.class.php');
		require_once(AgaviConfig::get('core.app_dir') . '/config/ConfigValueHolder.class.php');
		require_once(AgaviConfig::get('core.app_dir') . '/config/ConfigParser.class.php');
		require_once(AgaviConfig::get('core.app_dir') . '/config/XmlConfigParser.class.php');

		// manually create our config_handlers.ini handler
		self::$handlers['config_handlers.xml'] = new AgaviConfigHandlersConfigHandler();
		self::$handlers['config_handlers.xml']->initialize();

		// application configuration handlers
		require_once(AgaviConfigCache::checkConfig('config/config_handlers.xml'));

		// module level configuration handlers
		// are gone :)
	}

	/**
	 * Write a cache file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that 
	 *                    will be written.
	 * @param      string Data to be written to the cache file.
	 * @param      string Should we append the data?
	 *
	 * @throws     <b>AgaviCacheException</b> If the cache file cannot be written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private static function writeCacheFile ($config, $cache, &$data, $append)
	{

		$flags = ($append) ? FILE_APPEND : 0;
		
		@mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR);

		if (@file_put_contents($cache, $data, $flags) === false)
		{

			// cannot write cache file
			$error = 'Failed to write cache file "%s" generated from ' .
				     'configuration file "%s"';
			$error = sprintf($error, $cache, $config);

			throw new AgaviCacheException($error);

		}

	}



	public static function parseConfig($config, $autoloadParser = true)
	{
		static $parsers = array();

		$path = pathinfo($config);
		$ext = ucfirst(strtolower($path['extension']));
		if(!isset($parsers[$ext])) {
			$class = $ext . 'ConfigParser';
			if(!class_exists($class, $autoloadParser)) {
				$class = 'Agavi' . $class;
				if(!class_exists($class, $autoloadParser)) {
					throw new AgaviConfigurationException('Couldn\'t find parser for file extension .' . $path['extension']);
				}
			}

			$parsers[$ext] = new $class();
		}

		return $parsers[$ext]->parse($config);
	}

}

?>

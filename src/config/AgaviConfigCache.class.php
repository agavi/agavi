<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
final class AgaviConfigCache
{
	const CACHE_SUBDIR = 'config';

	/**
	 * @var        array An array of AgaviConfigHandlers
	 */
	private static $handlers = null;

	/**
	 * Load a configuration handler.
	 *
	 * @param      string The handler to use when parsing a configuration file.
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that
	 *                    will be written.
	 * @param      string The context which we're currently running.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If a requested configuration
	 *                                                file does not have an
	 *                                                associated config handler.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private static function callHandler($name, $config, $cache, $context)
	{
		if(self::$handlers === null) {
			// we need to load the handlers first
			self::$handlers = array();
			self::loadConfigHandlers();
		}

		// grab the base name of the handler
		$basename = basename($name);

		$handlerInfo = null;

		if(isset(self::$handlers[$name])) {
			// we have a handler associated with the full configuration path
			$handlerInfo = self::$handlers[$name];
		} elseif(isset(self::$handlers[$basename])) {
			// we have a handler associated with the configuration base name
			$handlerInfo = self::$handlers[$basename];
		} else {
			// let's see if we have any wildcard handlers registered that match
			// this basename
			foreach(self::$handlers as $key => $value)	{
				// replace wildcard chars in the configuration and create the pattern
				$pattern = sprintf('#%s#', str_replace('\*', '.*?', preg_quote($key)));

				if(preg_match($pattern, $name)) {
					$handlerInfo = $value;
					break;
				}
			}
		}

		if($handlerInfo === null) {
			// we do not have a registered handler for this file
			$error = 'Configuration file "%s" does not have a registered handler';
			$error = sprintf($error, $config);
			throw new AgaviConfigurationException($error);
		}

		// call the handler and retrieve the cache data
		$handler = new $handlerInfo['class'];
		if($handler instanceof AgaviIXmlConfigHandler) {
			// a new-style config handler
			// it does not parse the config itself; instead, it is given an array of parsed DOM documents (with parents!)
			$doc = AgaviXmlConfigParser::run($config, AgaviConfig::get('core.environment'), $context, $handlerInfo['validations'], $handlerInfo['transformations']);

			if($context !== null) {
				$context = AgaviContext::getInstance($context);
			}

			$handler->initialize($context, $handlerInfo['parameters']);

			try {
				$data = $handler->execute($doc);
			} catch(AgaviException $e) {
				throw new $e(sprintf("Compliation of configuration file '%s' failed for the following reason(s):\n\n%s", $config, $e->getMessage()));
			}
		} else {
			$validationFile = null;
			if(isset($handlerInfo['validations'][AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][0])) {
				$validationFile = $handlerInfo['validations'][AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][0];
			}
			$handler->initialize($validationFile, null, $handlerInfo['parameters']);
			$data = $handler->execute($config, $context);
		}

		self::writeCacheFile($config, $cache, $data, false);
	}

	/**
	 * Check to see if a configuration file has been modified and if so
	 * recompile the cache file associated with it.
	 *
	 * If the configuration file path is relative, the path itself is relative
	 * to the Agavi "core.app_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 * @param      string An optional context name for which the config should be
	 *                    read.
	 *
	 * @return     string An absolute filesystem path to the cache filename
	 *                    associated with this specified configuration file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function checkConfig($config, $context = null)
	{
		$config = AgaviToolkit::normalizePath($config);
		// the full filename path to the config, which might not be what we were given.
		$filename = AgaviToolkit::isPathAbsolute($config) ? $config : AgaviToolkit::normalizePath(AgaviConfig::get('core.app_dir')) . '/' . $config;

		if(!is_readable($filename)) {
			throw new AgaviUnreadableException('Configuration file "' . $filename . '" does not exist or is unreadable.');
		}

		// the cache filename we'll be using
		$cache = self::getCacheName($config, $context);

		if(self::isModified($filename, $cache)) {
			// configuration file has changed so we need to reparse it
			self::callHandler($config, $filename, $cache, $context);
		}

		return $cache;
	}

	/**
	 * Check if the cached version of a file is up to date.
	 *
	 * @param      string The source file.
	 * @param      string The name of the cached version.
	 *
	 * @return     bool Whether or not the cached file must be updated.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function isModified($filename, $cachename)
	{
		return (!is_readable($cachename) || filemtime($filename) > filemtime($cachename));
	}

	/**
	 * Clear all configuration cache files.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function clear()
	{
		AgaviToolkit::clearCache(self::CACHE_SUBDIR);
	}

	/**
	 * Clear all configuration cache files.
	 *
	 * @param      string The subdirectory to clear in the cache directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @param      string A context name.
	 *
	 * @return     string An absolute filesystem path to a cache filename.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function getCacheName($config, $context = null)
	{
		$environment = AgaviConfig::get('core.environment');

		if(strlen($config) > 3 && ctype_alpha($config{0}) &&	$config{1} == ':' && ($config{2} == '\\' || $config{2} == '/')) {
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
	 * to the Agavi "core.app_dir" application setting.
	 *
	 * @param      string A filesystem path to a configuration file.
	 * @param      string A context name.
	 * @param      bool   Only allow this configuration file to be included once
	 *                    per request?
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function load($config, $context = null, $once = true)
	{
		$cache = self::checkConfig($config, $context);

		if($once) {
			include_once($cache);
		} else {
			include($cache);
		}

	}

	/**
	 * Load all configuration application and module level handlers.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If a configuration related
	 *                                                error occurs.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private static function loadConfigHandlers()
	{
		$agaviDir = AgaviConfig::get('core.agavi_dir');
		// since we only need the parser and handlers when the config is not cached
		// it is sufficient to include them at this stage
		require_once($agaviDir . '/config/AgaviILegacyConfigHandler.interface.php');
		require($agaviDir . '/config/AgaviIXmlConfigHandler.interface.php');
		require_once($agaviDir . '/config/AgaviBaseConfigHandler.class.php');
		require_once($agaviDir . '/config/AgaviConfigHandler.class.php');
		require($agaviDir . '/config/AgaviXmlConfigHandler.class.php');
		require($agaviDir . '/config/AgaviAutoloadConfigHandler.class.php');
		require($agaviDir . '/config/AgaviConfigHandlersConfigHandler.class.php');
		require($agaviDir . '/config/AgaviConfigValueHolder.class.php');
		require($agaviDir . '/config/AgaviConfigParser.class.php');
		require($agaviDir . '/config/AgaviXmlConfigParser.class.php');
		// extended DOM* classes
		require($agaviDir . '/config/dom/AgaviXmlConfigDomAttr.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomCharacterData.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomComment.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomDocument.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomDocumentFragment.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomDocumentType.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomElement.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomEntity.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomEntityReference.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomNode.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomNotation.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomProcessingInstruction.class.php');
		require($agaviDir . '/config/dom/AgaviXmlConfigDomText.class.php');

		// manually create our config_handlers.xml handler
		self::$handlers['config_handlers.xml'] = array(
			'class' => 'AgaviConfigHandlersConfigHandler',
			'parameters' => array(
			),
			'validations' => array(
				AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA => array(
					$agaviDir . '/config/xsd/config_handlers.xsd',
				),
			),
		);

		$cfg = AgaviConfig::get('core.config_dir') . '/config_handlers.xml';
		if(!is_readable($cfg)) {
			$cfg = AgaviConfig::get('core.system_config_dir') . '/config_handlers.xml';
		}
		// application configuration handlers
		require(AgaviConfigCache::checkConfig($cfg));
	}

	/**
	 * Write a cache file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that
	 *                    will be written.
	 * @param      string Data to be written to the cache file.
	 * @param      bool   Should we append the data?
	 *
	 * @throws     <b>AgaviCacheException</b> If the cache file cannot be written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function writeCacheFile($config, $cache, $data, $append = false)
	{
		$perms = fileperms(AgaviConfig::get('core.cache_dir')) ^ 0x4000;

		$flags = LOCK_EX | (($append) ? FILE_APPEND : 0);

		AgaviToolkit::mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR, $perms);

		if(@file_put_contents($cache, $data, $flags) === false) {
			// cannot write cache file
			$error = 'Failed to write cache file "%s" generated from ' . 'configuration file "%s".';
			$error .= "\n\n Please make sure the directory \"%s\" is writeable by the web server.";
			$error = sprintf($error, $cache, $config, AgaviConfig::get('core.cache_dir'));

			throw new AgaviCacheException($error);
		} else {
			chmod($cache, $perms);
		}
	}

	/**
	 * Parses a config file with the ConfigParser for the extension of the given
	 * file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      bool Whether the config parser class should be autoloaded if
	 *                  the class doesn't exist.
	 * @param      string A path to a validation file for this config file.
	 * @param      string A class name which specifies an parser to be used.
	 *
	 * @return     AgaviConfigValueHolder An abstract representation of the
	 *                                    config file.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If the parser for the
	 *             extension couldn't be found.
	 *
	 * @deprecated New-style config handlers don't call this method anymore. To be
	 *             removed in Agavi 1.1
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function parseConfig($config, $autoloadParser = true, $validationFile = null, $parserClass = null)
	{
		$parser = new AgaviConfigParser();

		return $parser->parse($config, $validationFile);
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
class AgaviConfigCache
{
	const CACHE_SUBDIR = 'config';

	/**
	 * @var        array An array of config handler instructions.
	 */
	protected static $handlers = null;

	/**
	 * @var        array A string=>bool array containing config handler files and
	 *                   their loaded status.
	 */
	protected static $handlerFiles = array();

	/**
	 * @var        bool Whether there is an entry in self::$handlerFiles that
	 *                  needs processing.
	 */
	protected static $handlersDirty = true;
	
	/**
	 * @var        bool Whether the config handler files have been required.
	 */
	protected static $filesIncluded = false;

	/**
	 * Load a configuration handler.
	 *
	 * @param      string The path of the originally requested configuration file.
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An absolute filesystem path to the cache file that
	 *                    will be written.
	 * @param      string The context which we're currently running.
	 * @param      array  Optional config handler info array.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If a requested configuration
	 *                                                file does not have an
	 *                                                associated config handler.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      0.9.0
	 */
	protected static function callHandler($name, $config, $cache, $context, array $handlerInfo = null)
	{
		self::setupHandlers();
		
		if(null === $handlerInfo) {
			// we need to load the handlers first
			$handlerInfo = self::getHandlerInfo($name);
		}

		if($handlerInfo === null) {
			// we do not have a registered handler for this file
			$error = 'Configuration file "%s" does not have a registered handler';
			$error = sprintf($error, $name);
			throw new AgaviConfigurationException($error);
		}
		
		$data = self::executeHandler($config, $context, $handlerInfo);
		self::writeCacheFile($config, $cache, $data, false);
	}

	/**
	 * Set up all config handler definitions.
	 * 
	 * Checks whether the handlers have been loaded or the dirtyHandlers flat is
	 * set, and loads any handler that has not been loaded.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0
	 */
	protected static function setupHandlers()
	{
		self::loadConfigHandlers();
		
		if(self::$handlersDirty) {
			// set handlersdirty to false, prevent an infinite loop
			self::$handlersDirty = false;
			// load additional config handlers
			foreach(self::$handlerFiles as $filename => &$loaded) {
				if(!$loaded) {
					self::loadConfigHandlersFile($filename);
					$loaded = true;
				}
			}
		}
	}
	
	/**
	 * Fetch the handler information for the given filename.
	 * 
	 * @param        string The name of the config file (partial path).
	 * 
	 * @return       array  The handler info.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0
	 */
	protected static function getHandlerInfo($name)
	{
		// grab the base name of the originally requested config path
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
				$pattern = sprintf('#%s#', str_replace('\*', '.*?', preg_quote($key, '#')));

				if(preg_match($pattern, $name)) {
					$handlerInfo = $value;
					break;
				}
			}
		}
		
		return $handlerInfo;
	}
	
	/**
	 * Execute the config handler for the given file.
	 * 
	 * @param        string The path to the config file (full path).
	 * @param        string The context which we're currently running.
	 * @param        array  The config handler info.
	 * 
	 * @return       string The compiled data.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0
	 */
	protected static function executeHandler($config, $context, array $handlerInfo)
	{
		// call the handler and retrieve the cache data
		$handler = new $handlerInfo['class'];
		if($handler instanceof AgaviIXmlConfigHandler) {
			// a new-style config handler
			// it does not parse the config itself; instead, it is given a complete and merged DOM document
			$doc = AgaviXmlConfigParser::run($config, AgaviConfig::get('core.environment'), $context, $handlerInfo['transformations'], $handlerInfo['validations']);

			if($context !== null) {
				$context = AgaviContext::getInstance($context);
			}

			$handler->initialize($context, $handlerInfo['parameters']);

			try {
				$data = $handler->execute($doc);
			} catch(AgaviException $e) {
				throw new $e(sprintf("Compilation of configuration file '%s' failed for the following reason(s):\n\n%s", $config, $e->getMessage()));
			}
		} else {
			$validationFile = null;
			if(isset($handlerInfo['validations'][AgaviXmlConfigParser::STAGE_SINGLE][AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER][AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][0])) {
				$validationFile = $handlerInfo['validations'][AgaviXmlConfigParser::STAGE_SINGLE][AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER][AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][0];
			}
			$handler->initialize($validationFile, null, $handlerInfo['parameters']);
			$data = $handler->execute($config, $context);
		}
		
		return $data;
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

		if(strlen($config) > 3 && ctype_alpha($config[0]) && $config[1] == ':' && ($config[2] == '\\' || $config[2] == '/')) {
			// file is a windows absolute path, strip off the drive letter
			$config = substr($config, 3);
		}

		// replace unfriendly filename characters with an underscore and postfix the name with a php extension
		// see http://trac.agavi.org/wiki/RFCs/Ticket932 for an explanation how cache names are constructed
		$cacheName = sprintf(
			'%1$s_%2$s.php',
			preg_replace(
				'/[^\w-_.]/i', 
				'_', 
				sprintf(
					'%1$s_%2$s_%3$s', 
					basename($config), 
					$environment, 
					$context
				)
			),
			sha1(
				sprintf(
					'%1$s_%2$s_%3$s',
					$config,
					$environment,
					$context
				)
			)
		);
		
		return AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . $cacheName;
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
	protected static function loadConfigHandlers()
	{
		if(self::$handlers !== null) {
			return;
		} else {
			self::$handlers = array();
		}
		
		// some checks first
		if(!defined('LIBXML_DOTTED_VERSION') || (!AgaviConfig::get('core.ignore_broken_libxml', false) && !version_compare(LIBXML_DOTTED_VERSION, '2.6.16', 'gt'))) {
			throw new AgaviException("A libxml version greater than 2.6.16 is highly recommended. With version 2.6.16 and possibly later releases, validation of XML configuration files will not work and Form Population Filter will eventually fail randomly on some documents due to *severe bugs* in older libxml releases (2.6.16 was released in November 2004, so it is really getting time to update).\n\nIf you still would like to try your luck, disable this message by doing\nAgaviConfig::set('core.ignore_broken_libxml', true);\nand\nAgaviConfig::set('core.skip_config_validation', true);\nbefore calling\nAgavi::bootstrap();\nin index.php (app/config.php is not the right place for this).\n\nBut be advised that you *will* run into segfaults and other sad situations eventually, so what you should really do is upgrade your libxml install.");
		}
		
		$agaviDir = AgaviConfig::get('core.agavi_dir');
		
		// :NOTE: fgilcher, 2008-12-03
		// we need this method reentry safe for unit testing
		// sorry for the testing code in the class, but I don't have
		// any other idea to solve the issue
		if(!self::$filesIncluded) {
			// since we only need the parser and handlers when the config is not cached
			// it is sufficient to include them at this stage
			require_once($agaviDir . '/config/AgaviILegacyConfigHandler.interface.php');
			require_once($agaviDir . '/config/AgaviIXmlConfigHandler.interface.php');
			require_once($agaviDir . '/config/AgaviBaseConfigHandler.class.php');
			require_once($agaviDir . '/config/AgaviConfigHandler.class.php');
			require_once($agaviDir . '/config/AgaviXmlConfigHandler.class.php');
			require_once($agaviDir . '/config/AgaviAutoloadConfigHandler.class.php');
			require_once($agaviDir . '/config/AgaviConfigHandlersConfigHandler.class.php');
			require_once($agaviDir . '/config/AgaviConfigValueHolder.class.php');
			require_once($agaviDir . '/config/AgaviConfigParser.class.php');
			require_once($agaviDir . '/config/AgaviXmlConfigParser.class.php');
			// extended DOM* classes
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomAttr.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomCharacterData.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomComment.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomDocument.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomDocumentFragment.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomDocumentType.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomElement.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomEntity.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomEntityReference.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomNode.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomNotation.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomProcessingInstruction.class.php');
			require_once($agaviDir . '/config/util/dom/AgaviXmlConfigDomText.class.php');
			// schematron processor
			require_once($agaviDir . '/config/util/schematron/AgaviXmlConfigSchematronProcessor.class.php');
			// extended XSL* classes
			if(!AgaviConfig::get('core.skip_config_transformations', false)) {
				if(!extension_loaded('xsl')) {
					throw new AgaviConfigurationException("You do not have the XSL extension for PHP (ext/xsl) installed or enabled. The extension is used by Agavi to perform XSL transformations in the configuration system to guarantee forwards compatibility of applications.\n\nIf you do not want to or can not install ext/xsl, you may disable all transformations by setting\nAgaviConfig::set('core.skip_config_transformations', true);\nbefore calling\nAgavi::bootstrap();\nin index.php (app/config.php is not the right place for this because this is a setting that's specific to your environment or machine).\n\nKeep in mind that disabling transformations mean you *have* to use the latest configuration file formats and namespace versions. Also, certain additional configuration file validations implemented via Schematron will not be performed.");
				}
				// kill a bunch of kittens thanks to http://trac.agavi.org/ticket/1038...
				$hopeless = version_compare(PHP_VERSION, '5.2.9', '<');
				if($hopeless) {
					$crapfest = error_reporting(error_reporting() & ~E_STRICT);
				}
				require($agaviDir . '/config/util/xsl/AgaviXmlConfigXsltProcessor.class.php');
				if($hopeless) {
					// ... and resurrect them (breathe, kitty, breathe, damnit!)
					error_reporting($crapfest);
				}
			}
			self::$filesIncluded = true;
		}
		
		// manually create our config_handlers.xml handler
		self::$handlers['config_handlers.xml'] = array(
			'class' => 'AgaviConfigHandlersConfigHandler',
			'parameters' => array(
			),
			'transformations' => array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					$agaviDir . '/config/xsl/config_handlers.xsl',
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
				),
			),
			'validations' => array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(
					),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array(
						AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA => array(
							$agaviDir . '/config/xsd/config_handlers.xsd',
						),
						AgaviXmlConfigParser::VALIDATION_TYPE_SCHEMATRON => array(
							$agaviDir . '/config/sch/config_handlers.sch',
						),
					),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			),
		);

		$cfg = AgaviConfig::get('core.config_dir') . '/config_handlers.xml';
		if(!is_readable($cfg)) {
			$cfg = AgaviConfig::get('core.system_config_dir') . '/config_handlers.xml';
		}
		// application configuration handlers
		self::loadConfigHandlersFile($cfg);
	}
	
	/**
	 * Load the config handlers from the given config file.
	 * Existing handlers will not be overwritten.
	 * 
	 * @param      string The path to a config_handlers.xml file.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function loadConfigHandlersFile($cfg)
	{
		self::$handlers = (array)self::$handlers + include(AgaviConfigCache::checkConfig($cfg));
	}

	/**
	 * Schedules a config handlers file to be loaded.
	 * 
	 * @param      string The path to a config_handlers.xml file.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public static function addConfigHandlersFile($filename)
	{
		if(!isset(self::$handlerFiles[$filename])) {
			if(!is_readable($filename)) {
				throw new AgaviUnreadableException('Configuration file "' . $filename . '" does not exist or is unreadable.');
			}
			
			self::$handlerFiles[$filename] = false;
			self::$handlersDirty = true;
		}
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

		$cacheDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR;

		AgaviToolkit::mkdir($cacheDir, $perms);

		if($append && is_readable($cache)) {
			$data = file_get_contents($cache) . $data;
		}

		$tmpName = tempnam($cacheDir, basename($cache));
		if(@file_put_contents($tmpName, $data) !== false) {
			// that worked, but that doesn't mean we're safe yet
			// first, we cannot know if the destination directory really was writeable, as tempnam() falls back to the system temp dir
			// second, with php < 5.2.6 on win32 renaming to an already existing file doesn't work, but copy does
			// so we simply assume that when rename() fails that we are on win32 and try to use copy() followed by unlink()
			// if that also fails, we know something's odd
			if(@rename($tmpName, $cache) || (@copy($tmpName, $cache) && unlink($tmpName))) {
				// alright, it did work after all. chmod() and bail out.
				chmod($cache, $perms);
				return;
			}
		}
		
		// still here?
		// that means we could not write the cache file
		$error = 'Failed to write cache file "%s" generated from ' . 'configuration file "%s".';
		$error .= "\n\n";
		$error .= 'Please make sure you have set correct write permissions for directory "%s".';
		$error = sprintf($error, $cache, $config, AgaviConfig::get('core.cache_dir'));
		throw new AgaviCacheException($error);
	}

	/**
	 * Parses a config file with the ConfigParser for the extension of the given
	 * file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      bool   Whether the config parser class should be autoloaded if
	 *                    the class doesn't exist.
	 * @param      string A path to a validation file for this config file.
	 * @param      string A class name which specifies an parser to be used.
	 *
	 * @return     AgaviConfigValueHolder An abstract representation of the
	 *                                    config file.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If the parser for the
	 *             extension couldn't be found.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated New-style config handlers don't call this method anymore. To be
	 *             removed in Agavi 1.1
	 */
	public static function parseConfig($config, $autoloadParser = true, $validationFile = null, $parserClass = null)
	{
		$parser = new AgaviConfigParser();

		return $parser->parse($config, $validationFile);
	}
}

?>
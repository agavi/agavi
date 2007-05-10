<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * Pre-initialization script.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
final class Agavi
{
	/**
	 * @var        array An assoc array of classes and files used for autoloading.
	 */
	public static $autoloads = null;
	
	/**
	 * Handles autoloading of classes
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function __autoload($class)
	{
		if(self::$autoloads === null) {
			self::$autoloads = array();
			// catch parse errors of autoload.xml
			try {
				$cfg = AgaviConfig::get('core.config_dir') . '/autoload.xml';
				if(!is_readable($cfg)) {
					$cfg = $cfg = AgaviConfig::get('core.system_config_dir') . '/autoload.xml';
					if(!is_readable($cfg)) {
						return;
					}
				}
				include(AgaviConfigCache::checkConfig($cfg));
			} catch(Exception $e) {
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
		
		if(isset(self::$autoloads[$class])) {
			// class exists, let's include it
			require(self::$autoloads[$class]);
		}
		
		/*	
			If the class doesn't exist in autoload.xml there's not a lot we can do. Because 
			PHP's class_exists resorts to __autoload we cannot throw exceptions
			for this might break some 3rd party lib autoloading mechanism.
		*/
	}
	
	/**
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function bootstrap($environment = null)
	{
		try {
			// required classes for this file and ConfigCache to run
			require(AgaviConfig::get('core.agavi_dir') . '/util/AgaviInflector.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/util/AgaviVirtualArrayPath.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/util/AgaviParameterHolder.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/config/AgaviConfigCache.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviAutoloadException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviCacheException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviConfigurationException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviUnreadableException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviParseException.class.php');
			require(AgaviConfig::get('core.agavi_dir') . '/util/AgaviToolkit.class.php');
			
			if(AgaviConfig::has('core.environment') && AgaviConfig::isReadonly('core.environment')) {
				$environment = AgaviConfig::get('core.environment');
			} else {
				if($environment === null) {
					throw new AgaviException('You must supply an environment name to Agavi::bootstrap() or set the name of the default environment to be used in the configuration directive "core.environment".');
				}
				AgaviConfig::set('core.environment', $environment, true, true);
			}
			
			AgaviConfig::set('core.debug', false, false);
			
			if(!AgaviConfig::has('core.app_dir')) {
				throw new AgaviException('Configuration directive "core.app_dir" not defined, terminating...');
			}
			
			// define a few filesystem paths
			AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.app_dir') . '/cache', false, true);
			
			AgaviConfig::set('core.config_dir', AgaviConfig::get('core.app_dir') . '/config', false, true);
			
			AgaviConfig::set('core.system_config_dir', AgaviConfig::get('core.agavi_dir') . '/config/defaults', false, true);
			
			AgaviConfig::set('core.lib_dir', AgaviConfig::get('core.app_dir') . '/lib', false, true);
			
			AgaviConfig::set('core.model_dir', AgaviConfig::get('core.app_dir') . '/models', false, true);
			
			AgaviConfig::set('core.module_dir', AgaviConfig::get('core.app_dir') . '/modules', false, true);
			
			AgaviConfig::set('core.template_dir', AgaviConfig::get('core.app_dir') . '/templates', false, true);
			
			AgaviConfig::set('core.cldr_dir', AgaviConfig::get('core.agavi_dir') . '/translation/data', false, true);
			
			// ini settings
			ini_set('magic_quotes_runtime', AgaviConfig::get('php.magic_quotes_runtime', false));
			ini_set('unserialize_callback_func', AgaviConfig::get('php.unserialize_callback_func', '__autoload'));
			
			// load base settings
			AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/settings.xml');

			// clear our cache if the conditions are right
			if(AgaviConfig::get('core.debug'))	{
				AgaviToolkit::clearCache();

				// load base settings
				AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/settings.xml');
			}

			$compile = AgaviConfig::get('core.config_dir') . '/compile.xml';
			if(!is_readable($compile)) {
				$compile = AgaviConfig::get('core.system_config_dir') . '/compile.xml';
			}
			// required classes for the framework
			AgaviConfigCache::import($compile);

		} catch(Exception $e) {
			AgaviException::printStackTrace($e);
		}
	}
}

?>
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
 * Pre-initialization script.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class Agavi
{
	/**
	 * An associative arrays of class and file names used by __autoload().
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static $autoloads = null;
	
	/**
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	function bootstrap($environment = null)
	{
		try {
			
			if(AgaviConfig::has('core.environment') && AgaviConfig::isReadonly('core.environment')) {
				$environment = AgaviConfig::get('core.environment');
			} else {
				if($environment === null) {
					$environment = 'stdenv';
				}
				AgaviConfig::set('core.environment', $environment, true, true);
			}
			
			AgaviConfig::set('core.debug', false, false);
			
			if(!AgaviConfig::has('core.webapp_dir')) {
				throw new AgaviException('Configuration directive "core.webapp_dir" not defined, terminating...');
			}
			
			// define a few filesystem paths
			AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.webapp_dir') . '/cache', false);
			
			AgaviConfig::set('core.config_dir', AgaviConfig::get('core.webapp_dir') . '/config', false);
			
			AgaviConfig::set('core.system_config_dir', AgaviConfig::get('core.agavi_dir') . '/config/defaults', false);
			
			AgaviConfig::set('core.lib_dir', AgaviConfig::get('core.webapp_dir') . '/lib', false);
			
			AgaviConfig::set('core.module_dir', AgaviConfig::get('core.webapp_dir') . '/modules', false);
			
			AgaviConfig::set('core.template_dir', AgaviConfig::get('core.webapp_dir') . '/templates', false);
			
			// ini settings
			ini_set('magic_quotes_runtime', AgaviConfig::get('php.magic_quotes_runtime', false));
			ini_set('unserialize_callback_func', AgaviConfig::get('php.unserialize_callback_func', '__autoload'));
			
			// required classes for this file and ConfigCache to run
			require_once(AgaviConfig::get('core.agavi_dir') . '/util/ParameterHolder.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/ConfigCache.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/ConfigHandler.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/AutoloadConfigHandler.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/IniConfigHandler.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AutoloadException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/CacheException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/ConfigurationException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/UnreadableException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/ParseException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/util/Toolkit.class.php');

			// load base settings
			AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/settings.xml');

			// clear our cache if the conditions are right
			if(AgaviConfig::get('core.debug'))	{
				AgaviToolkit::clearCache();

				// load base settings
				AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/settings.xml');
			}

			// required classes for the framework
			AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/compile.xml');

		} catch (Exception $e) {
			AgaviException::printStackTrace($e);
		}
	}
}

?>
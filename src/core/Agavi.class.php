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
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session
	 *
	 * @throws     AgaviException if an error occurs during the bootstrap sequence
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	function bootstrap($environment = null)
	{
		try {
			
			AgaviConfig::set('core.debug', false, false);
			
			if(!AgaviConfig::has('core.webapp_dir')) {
				throw new AgaviException('Configuration directive "core.webapp_dir" not defined, terminating...');
			}
			
			// define a few filesystem paths
			AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.webapp_dir') . '/cache', false);
			
			AgaviConfig::set('core.config_dir', AgaviConfig::get('core.webapp_dir') . '/config', false);
			
			AgaviConfig::set('core.lib_dir', AgaviConfig::get('core.lib_dir') . '/lib');
			
			AgaviConfig::set('core.modules_dir', AgaviConfig::get('core.webapp_dir') . '/modules');
			
			AgaviConfig::set('core.templates_dir', AgaviConfig::get('core.templates_dir') . '/templates');
			
			// ini settings
			ini_set('magic_quotes_runtime', AgaviConfig::get('php.magic_quotes_runtime', false));
			ini_set('unserialize_callback_func', AgaviConfig::get('php.unserialize_callback_func', '__autoload'));
			
			// required classes for this file and ConfigCache to run
			require_once(AgaviConfig::get('core.app_dir') . '/util/ParameterHolder.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/ConfigCache.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/ConfigHandler.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/ParameterParser.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/IniConfigHandler.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/AutoloadConfigHandler.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/config/RootConfigHandler.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/AgaviException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/AutoloadException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/CacheException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/ConfigurationException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/UnreadableException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/exception/ParseException.class.php');
			require_once(AgaviConfig::get('core.app_dir') . '/util/Toolkit.class.php');

			// clear our cache if the conditions are right
			if(AgaviConfig::get('core.debug'))	{
				AgaviToolkit::clearCache();
			}

			// load base settings
			AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/settings.ini');

			// required classes for the framework
			AgaviConfigCache::import(AgaviConfig::get('core.config_dir') . '/compile.conf');

		} catch (AgaviException $e) {
			$e->printStackTrace();
		} catch (Exception $e) {
			// unknown exception
			$e = new AgaviException($e->getMessage());
			$e->printStackTrace();
		}
	}
}

?>
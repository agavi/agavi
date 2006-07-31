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
final class Agavi
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
	public static function bootstrap($environment = null)
	{
		try {
			// required classes for this file and ConfigCache to run
			require_once(AgaviConfig::get('core.agavi_dir') . '/util/AgaviParameterHolder.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/AgaviConfigCache.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/AgaviConfigHandler.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/config/AgaviAutoloadConfigHandler.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviAutoloadException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviCacheException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviConfigurationException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviUnreadableException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/exception/AgaviParseException.class.php');
			require_once(AgaviConfig::get('core.agavi_dir') . '/util/AgaviToolkit.class.php');
			
			if(AgaviConfig::has('core.environment') && AgaviConfig::isReadonly('core.environment')) {
				$environment = AgaviConfig::get('core.environment');
			} else {
				if($environment === null) {
					trigger_error('You must supply an environment name to Agavi::bootstrap() or set the name of the default environment to be used in the configuration directive "core.environment".', E_USER_ERROR);
				}
				AgaviConfig::set('core.environment', $environment, true, true);
			}
			
			AgaviConfig::set('core.debug', false, false);
			
			if(!AgaviConfig::has('core.webapp_dir')) {
				throw new AgaviException('Configuration directive "core.webapp_dir" not defined, terminating...');
			}
			
			// define a few filesystem paths
			AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.webapp_dir') . '/cache', false, true);
			
			AgaviConfig::set('core.config_dir', AgaviConfig::get('core.webapp_dir') . '/config', false, true);
			
			AgaviConfig::set('core.system_config_dir', AgaviConfig::get('core.agavi_dir') . '/config/defaults', false, true);
			
			AgaviConfig::set('core.lib_dir', AgaviConfig::get('core.webapp_dir') . '/lib', false, true);
			
			AgaviConfig::set('core.model_dir', AgaviConfig::get('core.webapp_dir') . '/models', false, true);
			
			AgaviConfig::set('core.module_dir', AgaviConfig::get('core.webapp_dir') . '/modules', false, true);
			
			AgaviConfig::set('core.template_dir', AgaviConfig::get('core.webapp_dir') . '/templates', false, true);
			
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

		} catch (Exception $e) {
			AgaviException::printStackTrace($e);
		}
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * Main framework class used for autoloading and initial bootstrapping of Agavi.
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
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function bootstrap($environment = null)
	{
		// set up our __autoload
		spl_autoload_register(array('AgaviAutoloader', 'loadClass'));

		try {
			if($environment === null) {
				// no env given? let's read one from core.environment
				$environment = AgaviConfig::get('core.environment');
			} elseif(AgaviConfig::has('core.environment') && AgaviConfig::isReadonly('core.environment')) {
				// env given, but core.environment is read-only? then we must use that instead and ignore the given setting
				$environment = AgaviConfig::get('core.environment');
			}
			
			if($environment === null) {
				// still no env? oh man...
				throw new AgaviException('You must supply an environment name to Agavi::bootstrap() or set the name of the default environment to be used in the configuration directive "core.environment".');
			}
			
			// finally set the env to what we're really using now.
			AgaviConfig::set('core.environment', $environment, true, true);

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

			// autoloads first (will trigger the compilation of config_handlers.xml)
			$autoload = AgaviConfig::get('core.config_dir') . '/autoload.xml';
			if(!is_readable($autoload)) {
				$autoload = AgaviConfig::get('core.system_config_dir') . '/autoload.xml';
			}
			AgaviConfigCache::load($autoload);
			
			// load base settings
			AgaviConfigCache::load(AgaviConfig::get('core.config_dir') . '/settings.xml');

			// clear our cache if the conditions are right
			if(AgaviConfig::get('core.debug')) {
				AgaviToolkit::clearCache();

				// load base settings
				AgaviConfigCache::load(AgaviConfig::get('core.config_dir') . '/settings.xml');
			}

			$compile = AgaviConfig::get('core.config_dir') . '/compile.xml';
			if(!is_readable($compile)) {
				$compile = AgaviConfig::get('core.system_config_dir') . '/compile.xml';
			}
			// required classes for the framework
			AgaviConfigCache::load($compile);

		} catch(Exception $e) {
			AgaviException::render($e);
		}
	}
}

?>
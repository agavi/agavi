<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */

// load the AgaviConfig class
require(dirname(__FILE__) . '/config/AgaviConfig.class.php');

// check minimum PHP version
AgaviConfig::set('core.minimum_php_version', '5.3.0');
if(!version_compare(PHP_VERSION, AgaviConfig::get('core.minimum_php_version'), 'ge') ) {
	die('You must be using PHP version ' . AgaviConfig::get('core.minimum_php_version') . ' or greater.');
}

// define a few filesystem paths
AgaviConfig::set('core.agavi_dir', $agavi_config_directive_core_agavi_dir = dirname(__FILE__), true, true);

// default exception template
AgaviConfig::set('exception.default_template', $agavi_config_directive_core_agavi_dir . '/exception/templates/shiny.php');

// required files
require($agavi_config_directive_core_agavi_dir . '/version.php');
require($agavi_config_directive_core_agavi_dir . '/core/Agavi.class.php');
// required files for classes Agavi and ConfigCache to run
// consider this the bare minimum we need for bootstrapping
require($agavi_config_directive_core_agavi_dir . '/util/AgaviInflector.class.php');
require($agavi_config_directive_core_agavi_dir . '/util/AgaviArrayPathDefinition.class.php');
require($agavi_config_directive_core_agavi_dir . '/util/AgaviVirtualArrayPath.class.php');
require($agavi_config_directive_core_agavi_dir . '/util/AgaviParameterHolder.class.php');
require($agavi_config_directive_core_agavi_dir . '/config/AgaviConfigCache.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviException.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviAutoloadException.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviCacheException.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviConfigurationException.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviUnreadableException.class.php');
require($agavi_config_directive_core_agavi_dir . '/exception/AgaviParseException.class.php');
require($agavi_config_directive_core_agavi_dir . '/util/AgaviToolkit.class.php');

// clean up (we don't want collisions with whatever file included us, in case you were wondering about the ugly name of that var)
unset($agavi_config_directive_core_agavi_dir);

?>
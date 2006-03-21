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
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */

// load the AgaviConfig class
require_once(dirname(__FILE__) . '/config/AgaviConfig.class.php');

/*
	Minimum requirement check
	
	Things arent going to work unless we're running with php5,
	so dont assume we are. 
*/
AgaviConfig::set('core.minimum_php_version', '5.0.0');

if(!version_compare(PHP_VERSION, AgaviConfig::get('core.minimum_php_version'), 'ge') ) {
	die('You must be using PHP version ' . AgaviConfig::get('core.minimum_php_version') . ' or greater.');
}

// define a few filesystem paths
AgaviConfig::set('core.app_dir', dirname(__FILE__), false);

// required files
require(AgaviConfig::get('core.app_dir') . '/version.php');
require(AgaviConfig::get('core.app_dir') . '/core/Agavi.class.php');



/**
 * Handles autoloading of classes
 *
 * @param      string A class name.
 *
 * @return     void
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @since      0.9.0
 */
function __autoload($class)
{
	if(isset(Agavi::$autoloads[$class])) {
		// class exists, let's include it
		require($autoloads[$class]);
	}
	
	/*	
		If the class doesn't exist in autoload.xml there's not a lot we can do. Because 
		PHP's class_exists resorts to __autoload we cannot throw exceptions
		for this might break some 3rd party lib autoloading mechanism.
	*/

}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 *
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <impl@cynigram.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
final class AgaviBuild
{
	/**
	 * @var        array An associative array of classes and files that
	 *                   can be autoloaded.
	 */
	public static $autoloads = array(
		'AgaviCheck' => 'check/AgaviCheck.class.php',
		'AgaviFilesystemCheck' => 'check/AgaviFilesystemCheck.class.php',
		'AgaviProjectFilesystemCheck' => 'check/AgaviProjectFilesystemCheck.class.php',
		'AgaviModuleFilesystemCheck' => 'check/AgaviModuleFilesystemCheck.class.php',
		'AgaviTransform' => 'transform/AgaviTransform.class.php',
		'AgaviIdentifierTransform' => 'transform/AgaviIdentifierTransform.class.php',
	);

	/**
	 * Autoloads classes.
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Noah Fontes <impl@cynigram.com>
	 */
	public static function __autoload($class)
	{
		if(isset(self::$autoloads[$class])) {
			require(dirname(__FILE__) . '/' . self::$autoloads[$class]);
		}

		/* If the class isn't loaded by this method, the only other
		 * sane option is to simply let PHP handle it and hope another
		 * handler picks it up. */
	}

	/**
	 * Prepares the build environment classes for use.
	 */
	public static function bootstrap()
	{
		spl_autoload_register(array(self, '__autoload'));
	}
}

?>
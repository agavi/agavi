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
 * Build system utility class.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
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
	 * @var        bool Whether or not the build system has been bootstrapped yet.
	 */
	protected static $bootstrapped = false;
	
	/**
	 * @var        array An associative array of classes and files that
	 *                   can be autoloaded.
	 */
	public static $autoloads = array(
		'AgaviBuildException' => 'exception/AgaviBuildException.class.php',
		'AgaviCheck' => 'check/AgaviCheck.class.php',
		'AgaviFilesystemCheck' => 'check/AgaviFilesystemCheck.class.php',
		'AgaviProjectFilesystemCheck' => 'check/AgaviProjectFilesystemCheck.class.php',
		'AgaviModuleFilesystemCheck' => 'check/AgaviModuleFilesystemCheck.class.php',
		'AgaviTransform' => 'transform/AgaviTransform.class.php',
		'AgaviIdentifierTransform' => 'transform/AgaviIdentifierTransform.class.php',
		'AgaviArraytostringTransform' => 'transform/AgaviArraytostringTransform.class.php',
		'AgaviStringtoarrayTransform' => 'transform/AgaviStringtoarrayTransform.class.php',
		'AgaviEventBuildException' => 'event/AgaviEventBuildException.class.php',
		'AgaviIListener' => 'event/AgaviIListener.interface.php',
		'AgaviEventDispatcher' => 'event/AgaviEventDispatcher.class.php',
		'AgaviIEvent' => 'event/AgaviIEvent.interface.php',
		'AgaviEvent' => 'event/AgaviEvent.class.php',
		'AgaviProxyProject' => 'phing/AgaviProxyProject.class.php',
		'AgaviProxyXmlContext' => 'phing/AgaviProxyXmlContext.class.php',
		'AgaviProxyTarget' => 'phing/AgaviProxyTarget.class.php',
		'AgaviPhingEventDispatcherManager' => 'phing/AgaviPhingEventDispatcherManager.class.php',
		'AgaviPhingEventDispatcher' => 'phing/AgaviPhingEventDispatcher.class.php',
		'AgaviPhingEvent' => 'phing/AgaviPhingEvent.class.php',
		'AgaviPhingTargetEvent' => 'phing/AgaviPhingTargetEvent.class.php',
		'AgaviPhingTaskEvent' => 'phing/AgaviPhingTaskEvent.class.php',
		'AgaviPhingMessageEvent' => 'phing/AgaviPhingMessageEvent.class.php',
		'AgaviIPhingListener' => 'phing/AgaviIPhingListener.interface.php',
		'AgaviIPhingTargetListener' => 'phing/AgaviIPhingTargetListener.interface.php',
		'AgaviIPhingTaskListener' => 'phing/AgaviIPhingTaskListener.interface.php',
		'AgaviIPhingMessageListener' => 'phing/AgaviIPhingMessageListener.interface.php',
		'AgaviPhingTargetAdapter' => 'phing/AgaviPhingTargetAdapter.class.php',
		'AgaviPhingTaskAdapter' => 'phing/AgaviPhingTaskAdapter.class.php',
		'AgaviPhingMessageAdapter' => 'phing/AgaviPhingMessageAdapter.class.php',
		'AgaviBuildLogger' => 'phing/AgaviBuildLogger.php',
		'AgaviProxyBuildLogger' => 'phing/AgaviProxyBuildLogger.php'
	);

	/**
	 * Autoloads classes.
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
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
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public static function bootstrap()
	{
		if(self::$bootstrapped === false) {
			spl_autoload_register(array('AgaviBuild', '__autoload'));
		}
		
		self::$bootstrapped = true;
	}
	
	/**
	 * Retrieves whether the build system has been bootstrapped.
	 *
	 * @return     boolean True if the build system has been bootstrapped, false
	 *                     otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public static function isBootstrapped()
	{
		return self::$bootstrapped;
	}
}

?>
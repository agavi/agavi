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
 * AgaviAutoloader is an autoloader implementation with support for namespaces,
 * conforming to the PSR-0 standard. It also allows a plain mapping of class
 * names to file paths.
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviAutoloader
{
	/**
	 * @var        array An assoc array of classes and file paths for autoloading.
	 */
	public static $classes = array();
	
	/**
	 * @var        array An assoc array of namespaces and paths for autoloading.
	 */
	public static $namespaces = array();

	/**
	 * Add classes to the autoloader.
	 *
	 * @param      array An array containing class names as keys and paths to the
	 *                   corresponding PHP files as values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public static function addClasses(array $map)
	{
		self::$classes = array_merge(self::$classes, $map);
	}

	/**
	 * Add namespaces to the autoloader.
	 *
	 * @param      array An array containing namespace prefixes as keys and paths
	 *                   to the corresponding directories containing files as
	 *                   values. Namespace prefixes must not contain a trailing
	 *                   backslash.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public static function addNamespaces(array $map)
	{
		self::$namespaces = array_merge(self::$namespaces, $map);
	}

	/**
	 * Handles autoloading of classes
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function loadClass($class)
	{
		if(isset(self::$classes[$class])) {
			// class exists, let's include it
			require(self::$classes[$class]);
			return true;
		}
		
		// nothing yet; let's see if it's in one of our namespace map paths
		$lastBackslash = strrpos($class, '\\');
		if($lastBackslash === false) {
			return false;
		}
		
		// split input into namespace and class name
		$namespace = substr($class, 0, $lastBackslash);
		$class = substr($class, $lastBackslash+1);
		
		foreach(self::$namespaces as $prefix => $path) {
			if(strpos($namespace . '\\', $prefix . '\\') === 0) { // make sure we terminate the prefix, or else a prefix like "Doc" would load "Doctrine"
				$file = str_replace('\\', DIRECTORY_SEPARATOR, substr($namespace, strlen($prefix))) // strip the prefix from the namespace and replace backslashes
				      . DIRECTORY_SEPARATOR
				      . str_replace('_', DIRECTORY_SEPARATOR, $class) // replace underscores in the class name in conformance with PSR-0
				      . '.php';
				// unconditionally load the file, but only use an include() just in case the file isn't there
				include($path . $file);
				return true;
			}
		}
		
		// If the class doesn't exist in autoload.xml there's not a lot we can do.
		// Hopefully, another registered autoloader will be able to help :)
		return false;
	}
}

?>
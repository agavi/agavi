<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviToolkit provides basic utility methods.
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
final class AgaviToolkit
{
	/**
	 * Determine if a filesystem path is absolute.
	 *
	 * @param      path A filesystem path.
	 *
	 * @return     bool true, if the path is absolute, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function isPathAbsolute($path)
	{

		if($path[0] == '/' || $path[0] == '\\' ||
			(
				strlen($path) >= 3 && ctype_alpha($path[0]) &&
				$path[1] == ':' &&
				($path[2] == '\\' || $path[2] == '/')
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get the heritage of a class
	 *
	 * @param      string $class A class to examine
	 *
	 * @return     array of classnames in the classes ancestry
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.10.0
	 */
	public static function classHeritage($class)
	{
		$heritage = array();
		while($class != '') {
			$class = get_parent_class($class);
			if($class) {
				array_unshift($heritage, $class);
			}
		}
		return $heritage;
	}
	
	/**
	 * Determine if a class is a subclass of another class
	 *
	 * @param      string $class A potential child class
	 * @param      string $parent A potential parent class
	 *
	 * @return     bool true, if the path is absolute, otherwise false.
	 *
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.10.0
	 */
	public static function isSubClass($class, $parent)
	{
		return in_array($parent, self::classHeritage($class));
	}
	
	/**
	 * Creates a directory without sucking at permissions.
	 * PHP mkdir() doesn't do what you tell it to, it takes umask into account.
	 *
	 * @param      string   The path name.
	 * @param      int      The mode. Really.
	 * @param      bool     Recursive or not.
	 * @param      resource A Context.
	 *
	 * @return     bool The mkdir return value.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function mkdir($path, $mode = 0777, $recursive = false, $context = null)
	{
		if($context !== null) {
			$retval = @mkdir($path, $mode, $recursive, $context);
		} else {
			$retval = @mkdir($path, $mode, $recursive);
		}
		if($retval) {
			chmod($path, $mode);
		}
		return $retval;
	}

	/**
	 * Deletes a specified path in the cache dir recursively. If a folder is given
	 * the contents of this folder and all sub-folders get erased, but not the
	 * folder itself.
	 *
	 * @param      string The path to remove
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function clearCache($path = '')
	{
		$ignores = array('.', '..', '.svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr');
		static $SPL_RIT_CHILD_FIRST = null;
		if(!isset($SPL_RIT_CHILD_FIRST)) {
			if(defined('RecursiveIteratorIterator::CHILD_FIRST')) {
				$SPL_RIT_CHILD_FIRST = RecursiveIteratorIterator::CHILD_FIRST;
			} else {
				$SPL_RIT_CHILD_FIRST = RIT_CHILD_FIRST;
			}
		}
		$path = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $path));
		$path = realpath(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . $path);
		if($path === false) {
			return false;
		}
		if(is_file($path)) {
			@unlink($path);
		} else {
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), $SPL_RIT_CHILD_FIRST) as $iterator) {
				// omg, thanks spl for always using forward slashes ... even on windows
				$pathname = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $iterator->getPathname()));
				$continue = false;
				if(in_array($iterator->getFilename(), $ignores)) {
					$continue = true;
				} else {
					foreach($ignores as $ignore) {
						if(strpos($pathname, DIRECTORY_SEPARATOR . $ignore . DIRECTORY_SEPARATOR) !== false) {
							$continue = true;
							break;
						} elseif(strrpos($pathname, DIRECTORY_SEPARATOR . $ignore) == (strlen($pathname) - strlen(DIRECTORY_SEPARATOR . $ignore))) {
							// if we hit the directory itself it wont include a trailing /
							$continue = true;
							break;
						}
					}
				}
				if($continue) {
					continue;
				}
				if($iterator->isDir()) {
					@rmdir($pathname);
				} elseif($iterator->isFile()) {
					@unlink($pathname);
				}
			}
		}
	}

}

?>
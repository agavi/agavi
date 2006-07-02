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
 * AgaviPath implements handling of virtual paths
 * 
 * This class does not implement real filesystem path handling, but uses virtual
 * paths. It is primary used in the validation system for handling arrays of
 * input. 
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviPath
{
	/**
	 * @var        bool is path absolut?
	 */
	private $Absolute = false;
	/**
	 * @var        array path components (the directories) 
	 */
	private $Dirs = array();
	
	/**
	 * constructor
	 * 
	 * @param      string $path path to be handled by the object
	 */
	public function __construct ($path)
	{
		if (strlen($path) == 0) {
			return;
		}
		
		$this->Absolute = ($path[0] == '/');
		$this->Dirs = array_filter(explode('/', $path), create_function('$a', 'return (strlen($a));'));
		
		$this->cleanPath();
	}
	
	/**
	 * cleans up the path (resolves '..')
	 */
	private function cleanPath ()
	{
		$dirs = array();
		foreach ($this->Dirs as $dir) {
			if ($dir == '' or $dir == '.') {
				continue;
			}
			if ($dir == '..') {
				if (!count($this->Dirs) and !$this->Absolute) {
					array_push($dirs, $dir);
				} elseif (count($this->Dirs) and $this->Dirs[count($this->Dirs)-1] == '..') {
					array_push($dirs, $dir);
				} elseif (count($this->Dirs)) {
					array_pop($this->Dirs);
				}
				
				continue;
			}
			array_push($dirs, $dir);
		}
		
		$this->Dirs = $dirs;
	}
	
	/**
	 * returns whether the path is absolute
	 * 
	 * @return     bool path is optional
	 */
	public function isAbsolute()
	{
		return $this->Absolute;
	}
	
	/**
	 * puts the path back into a string
	 * 
	 * @return     string path as string
	 */
	public function __toString ()
	{
		return (($this->Absolute) ? '/' : '').join('/', $this->Dirs);
	}
	
	/**
	 * returns the number of components the path has
	 * 
	 * @return     int number of components
	 */
	public function length ()
	{
		return count($this->Dirs);
	}
	
	/**
	 * returns the root component of the path
	 * 
	 * @param      bool $addSlashWhenAbsolute prepent '/' when the path is
	 *                                        absolut (defaults to false)
	 * 
	 * @return     string root component
	 */
	public function left ($addSlashWhenAbsolute = false)
	{
		if ($this->length()) {
			return $this->Dirs[0];
		} 
	}
	
	/**
	 * returns the last component of the path and deletes it from the path
	 * 
	 * @return     string last component
	 */
	public function pop ()
	{
		if (!count($this->Dirs)) {
			return NULL;
		}
		
		return array_pop($this->Dirs);
	}
	
	/**
	 * appends one or more components to the path
	 * 
	 * @param      string $path components to be added
	 */
	public function push ($path)
	{
		array_push($this->Dirs, array_filter(explode('/', $path), create_function('$a', 'return (strlen($a));')));
		$this->cleanPath();
	}
	
	/**
	 * returns the root component of the path and deletes it from the path
	 * 
	 * @param      bool $addSlashWhenAbsolute prepent '/' when the path is
	 *                                        absolut (defaults to false)
	 * 
	 * @return     string root component
	 */
	public function shift ($addSlashWhenAbsolute = false) {
		if (!count($this->Dirs)) {
			return NULL;
		}
		
		$ret = array_shift($this->Dirs);
		
		if ($this->Absolute) {
			$ret = (($addSlashWhenAbsolute) ? '/' : '').$ret;
			$this->Absolute = false;
		}
		
		return $ret;
	}
	
	/**
	 * fetches a value from an array following a given path
	 * 
	 * The array is walked by the path, starting at the root.
	 * e.g. /foo/bar means $array[foo][bar] and so on
	 * 
	 * @param      array  $array   Array where the value is fetched from
	 * @param      string $path    path that shows to the value
	 * @param      mixed  $default default value if the path points to no
	 *                             defined value
	 * 
	 * @return     mixed value in path or default
	 * 
	 * @see        setValueByPath()
	 */
	public static function &getValueByPath (&$array, $path, $default = NULL)
	{
		/*
		 * The array of references is a hack to avoid turning all
		 * arrays in $array into references... stupid php... 
		 */
		
		$a = array(&$array);
		$p = new AgaviPath($path);
		
		while ($name = $p->shift()) {
			if (!isset($a[count($a)-1][$name])) {
				return $default;
			}
			array_push($a, &$a[count($a)-1][$name]);
		}
		
		return $a[count($a)-1];
	}
	
	/**
	 * puts a value into an array following a given path
	 * 
	 * The path defines the position where the value shoul be saved.
	 * e.g. /foo/bar means $array[foo][bar] and so on
	 * 
	 * @param      array  $array Array where the value should be saved into
	 * @param      string $path  path that defines the position where to
	 *                           put the value
	 * @param      mixed  $value value
	 * 
	 * @see        getValueByPath()
	 */
	public static function setValueByPath (&$array, $path, $value)
	{
		/*
		 * The array of references is a hack to avoid turning all
		 * arrays in $array into references... stupid php... 
		 */
		
		$a = array(&$array);
		$p = new AgaviPath($path);
		
		while ($name = $p->shift()) {
			if (!isset($a[count($a)-1][$name])) {
				$a[count($a)-1][$name] = array();
			}
			array_push($a, &$a[count($a)-1][$name]);
		}
		
		$a[count($a)-1] = $value;
	}
}
?>
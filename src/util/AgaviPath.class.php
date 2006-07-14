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
	protected $absolute = false;
	/**
	 * @var        array path components (the directories) 
	 */
	protected $dirs = array();
	
	/**
	 * constructor
	 * 
	 * @param      string path to be handled by the object
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __construct($path)
	{
		if (strlen($path) == 0) {
			return;
		}
		
		$this->absolute = ($path[0] == '/');
		$this->dirs = array_filter(explode('/', $path), create_function('$a', 'return (strlen($a));'));
		
		$this->cleanPath();
	}
	
	/**
	 * cleans up the path (resolves '..')
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function cleanPath()
	{
		$dirs = array();
		foreach($this->dirs as $dir) {
			if($dir == '' or $dir == '.') {
				continue;
			}
			if($dir == '..') {
				if(!count($dirs) and !$this->absolute) {
					array_push($dirs, $dir);
				} elseif(count($dirs) and $dirs[count($dirs)-1] == '..') {
					array_push($dirs, $dir);
				} elseif(count($dirs)) {
					array_pop($dirs);
				}

				continue;
			}
			array_push($dirs, $dir);
		}
		
		$this->dirs = $dirs;
	}
	
	/**
	 * returns whether the path is absolute
	 * 
	 * @return     bool path is optional
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function isAbsolute()
	{
		return $this->absolute;
	}
	
	/**
	 * puts the path back into a string
	 * 
	 * @return     string path as string
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __toString()
	{
		return (($this->absolute) ? '/' : '').join('/', $this->dirs);
	}
	
	/**
	 * returns the number of components the path has
	 * 
	 * @return     int number of components
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function length()
	{
		return count($this->dirs);
	}
	
	/**
	 * returns the root component of the path
	 * 
	 * @param      bool prepend '/' when the path is absolut (defaults to false)
	 * 
	 * @return     string root component
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function left($addSlashWhenAbsolute = false)
	{
		if(!$this->length()) {
			return null;
		}

		$dir = $this->dirs[0];
		if($this->isAbsolute() and $addSlashWhenAbsolute) {
			return '/'.$dir;
		}

		if(strval(intval($dir)) == $dir) {
			return intval($dir);
		}

		return $dir;
		 
	}
	
	/**
	 * returns the last component of the path and deletes it from the path
	 * 
	 * @return     string last component
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function pop()
	{
		if(!count($this->dirs)) {
			return null;
		}
		
		$dir = array_pop($this->dirs);

		if(strval(intval($dir)) == $dir) {
			return intval($dir);
		} else {
			return $dir;
		}
	}
	
	/**
	 * appends one or more components to the path
	 * 
	 * @param      string components to be added
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function push($path)
	{
		$this->dirs = array_merge($this->dirs, array_filter(explode('/', $path), create_function('$a', 'return (strlen($a));')));
		$this->cleanPath();
	}
	
	/**
	 * returns the root component of the path and deletes it from the path
	 * 
	 * @param      bool prepent '/' when the path is absolut (defaults to false)
	 * 
	 * @return     string root component
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function shift($addSlashWhenAbsolute = false) {
		if(!count($this->dirs)) {
			return null;
		}
		
		$ret = array_shift($this->dirs);
		
		if($this->absolute) {
			$ret = (($addSlashWhenAbsolute) ? '/' : '').$ret;
			$this->absolute = false;
		}
		
		if(strval(intval($ret)) == $ret) {
			return intval($ret);
		} else {
			return $ret;
		}
	}
	
	/**
	 * fetches a value from an array following a given path
	 * 
	 * The array is walked by the path, starting at the root.
	 * e.g. /foo/bar means $array[foo][bar] and so on
	 * 
	 * @param      array  Array where the value is fetched from
	 * @param      string path that shows to the value
	 * @param      mixed  default value if the path points to no defined value
	 * 
	 * @return     mixed value in path or default
	 * 
	 * @see        setValueByPath()
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public static function &getValueByPath(&$array, $path, $default = null)
	{
		/*
		 * The array of references is a hack to avoid turning all
		 * arrays in $array into references... stupid php... 
		 */
		
		$a = array(&$array);
		$p = new AgaviPath($path);
		
		while(($name = $p->shift()) !== null) {
			if(!isset($a[count($a)-1][$name])) {
				return $default;
			}
			$a[] = &$a[count($a)-1][$name];
		}
		
		return $a[count($a)-1];
	}
	
	/**
	 * puts a value into an array following a given path
	 * 
	 * The path defines the position where the value shoul be saved.
	 * e.g. /foo/bar means $array[foo][bar] and so on
	 * 
	 * @param      array  Array where the value should be saved into
	 * @param      string path that defines the position where to put the value
	 * @param      mixed  value
	 * 
	 * @see        getValueByPath()
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public static function setValueByPath(&$array, $path, $value)
	{
		/*
		 * The array of references is a hack to avoid turning all
		 * arrays in $array into references... stupid php... 
		 */
		
		$a = array(&$array);
		$p = new AgaviPath($path);
		
		while($p->length() > 1) {
			$name = $p->shift();
			if(!is_array($a[count($a)-1])) {
				$a[count($a)-1] = array($name => array());
			} elseif(!isset($a[count($a)-1][$name])) {
				$a[count($a)-1][$name] = array();
			}
			$a[] = &$a[count($a)-1][$name];
		}
		
		$a[count($a)-1][$p->shift()] = $value;
	}
}
?>
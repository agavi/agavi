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
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviVirtualArrayPath
{
	/**
	 * @var        bool is path absolute?
	 */
	protected $absolute = false;
	/**
	 * @var        array array components
	 */
	protected $parts = array();
	
	/**
	 * constructor
	 * 
	 * @param      string path to be handled by the object
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($path)
	{
		if(strlen($path) == 0) {
			$this->absolute = true;
			return;
		}
		
		$parts = array();
		$this->absolute = ($path[0] != '[');
		if(($pos = strpos($path, '[')) === false) {
			$pos = strlen($path);
		}
		if($this->absolute) {
			$parts[] = substr($path, 0, $pos);
		}

		$parts = $this->getPartsFromPath($path);

		$this->absolute = $parts['absolute'];
		$this->parts = $parts['parts'];
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __toString()
	{
		$parts = $this->parts;
		if(count($parts) == 0) {
			return '';
		}

		$name = '';
		if($this->absolute) {
			$name = $parts[0];
			$parts = array_slice($parts, 1);
		}
		$path = '';
		if(count($parts)) {
			$path = sprintf('[%s]', implode('][', $parts));
		}

		return $name . $path;
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
		return count($this->parts);
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
	public function left($addBracketsWhenRelative = false)
	{
		if(!$this->length()) {
			return null;
		}

		$part = $this->parts[0];

		if(strval(intval($part)) == $part) {
			$part = intval($part);
		}

		if(!$this->absolute && $addBracketsWhenRelative) {
			$part = sprintf('[%s]', $part);
		}

		return $part;
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
		if(!$this->length()) {
			return null;
		}

		$part = array_pop($this->parts);

		if(strval(intval($part)) == $part) {
			return intval($part);
		} else {
			return $part;
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
		$parts = $this->getPartsFromPath($path);
		$this->parts = array_merge($this->parts, $parts['parts']);
	}

	public function pushRetNew($path)
	{
		$new = clone $this;
		$new->push($path);
		return $new;
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
	public function shift($addBracketsWhenRelative = false) {
		if(!$this->length()) {
			return null;
		}
		
		$ret = $this->left($addBracketsWhenRelative);

		array_shift($this->parts);

		if($this->absolute) {
			$this->absolute = false;
		}

		return $ret;
	}

	public function &getValueFromArray(&$array, $default = null)
	{
		$parts = $this->parts;

		$a = &$array;

		foreach($parts as $part)
		{
			if($part)
			{
				if(isset($a[$part])) {
					$a = &$a[$part];
				} else {
					//throw new AgaviException('The part: ' . $part . ' does not exist in the given array');
					return $default;
				}
			}
		}

		return $a;
	}

	public function setValueFromArray(&$array, $value)
	{
		$oldValue =& $this->getValueFromArray($array);
		$oldValue = $value;
	}

	
	public function getValueByChildPath($path, $array, $default = null)
	{
		$p = $this->pushRetNew($path);

		return $p->getValueFromArray($array, $default);
	}

	public function setValueByChildPath($path, &$array, $value)
	{
		$p = $this->pushRetNew($path);

		return $p->setValueFromArray($array, $value);
	}

	protected function getPartsFromPath($path)
	{
		$parts = array();
		$absolute = ($path[0] != '[');
		if(($pos = strpos($path, '[')) === false) {
			$parts[] = substr($path, 0, strlen($path));
		}
		else {
			if($absolute) {
				$parts[] = substr($path, 0, $pos);
			}

			$parts = array_merge($parts, explode('][', rtrim(ltrim(substr($path, $pos), '['), ']')));
		}

		return array('parts' => $parts, 'absolute' => $absolute);
	}
}
?>
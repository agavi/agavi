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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviVirtualArrayPath
{
	/**
	 * @var        bool Is path absolute?
	 */
	protected $absolute = false;
	/**
	 * @var        array Array components.
	 */
	protected $parts = array();
	
	/**
	 * constructor
	 * 
	 * @param      string The path to be handled by the object
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
		
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		
		$this->absolute = $parts['absolute'];
		$this->parts = $parts['parts'];
	}
	
	/**
	 * Returns whether the path is absolute.
	 * 
	 * @return     bool True if the path is absolute.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function isAbsolute()
	{
		return $this->absolute;
	}
	
	/**
	 * Returns the string representation of the path.
	 * 
	 * @return     string The path as string.
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
	 * Returns the number of components the path has.
	 * 
	 * @return     int The number of components.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function length()
	{
		return count($this->parts);
	}
	
	/**
	 * Returns the given component of the path.
	 * 
	 * @param      int Position of the component.
	 * 
	 * @return     string The component at the given position.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function get($position)
	{
		if($position < 0 || $position >= $this->length()) {
			return null;
		}

		$part = $this->parts[$position];

		if((string)(int)$part == $part) {
			$part = (int)$part;
		}

		return $part;
	}

	/**
	 * Returns the root component of the path.
	 * 
	 * @param      bool Whether brackets should be added around the component if
	 *                  this path is not absolute.
	 * 
	 * @return     string The root component.
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

		if((string)(int)$part == $part) {
			$part = (int)$part;
		}

		if(!$this->absolute && $addBracketsWhenRelative) {
			$part = sprintf('[%s]', $part);
		}

		return $part;
	}
	
	/**
	 * Returns the last component of the path and deletes it from the path.
	 * 
	 * @return     string The last component.
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

		if((string)(int)$part == $part) {
			return (int)$part;
		} else {
			return $part;
		}
	}
	
	/**
	 * Appends one or more components to the path.
	 * 
	 * @param      string The components to be added.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function push($path)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		$this->parts = array_merge($this->parts, $parts['parts']);
	}

	/**
	 * Clones this path, appends one or more components to it and returns it.
	 * 
	 * @param      string the components to be added.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function pushRetNew($path)
	{
		$new = clone $this;
		$new->push($path);
		return $new;
	}
	
	/**
	 * Returns the root component of the path and deletes it from the path.
	 * 
	 * @param      bool Whether brackets should be added around the component if
	 *                  this path is not absolute.
	 * 
	 * @return     string The root component.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function shift($addBracketsWhenRelative = false)
	{
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

	/**
	 * Prepends one or more components to the path.
	 * 
	 * @param      string The components to be prepended.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function unshift($path)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		$this->parts = array_merge($parts['parts'], $this->parts);
	}

	/**
	 * Checks if a value exists  at the path of this instance in the given array.
	 * 
	 * @param      array The array to check.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasValue(array &$array)
	{
		return AgaviArrayPathDefinition::hasValue($this->parts, $array);
	}

	/**
	 * Returns the value at the path of this instance in the given array.
	 * 
	 * @param      array The array to get the data from.
	 * @param      mixed The default value to be used if the path doesn't exist.
	 * 
	 * @return     mixed The value at the path.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getValue(array &$array, $default = null)
	{
		return AgaviArrayPathDefinition::getValue($this->parts, $array, $default);
	}

	/**
	 * Sets the value at the path of this instance in the given array.
	 * 
	 * @param      array The array to set the data in.
	 * @param      mixed The value to be set.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setValue(array &$array, $value)
	{
		AgaviArrayPathDefinition::setValue($this->parts, $array, $value);
	}

	/**
	 * Returns the value at the given child path of this instance in the given 
	 * array.
	 * 
	 * @param      string The child path appended to the path in this instance.
	 * @param      array The array to get the data from.
	 * @param      mixed The default value to be used if the path doesn't exist.
	 * 
	 * @return     mixed The value at the path.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getValueByChildPath($path, array &$array, $default = null)
	{
		$p = $this->pushRetNew($path);

		return $p->getValue($array, $default);
	}

	/**
	 * Sets the value at the given child path of this instance in the given array.
	 * 
	 * @param      string The child path appended to the path in this instance.
	 * @param      array The array to set the data in.
	 * @param      mixed The value to be set.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setValueByChildPath($path, array &$array, $value)
	{
		$p = $this->pushRetNew($path);

		$p->setValue($array, $value);
	}

	/**
	 * Checks if a value at the given child path exists in the given array.
	 * 
	 * @param      string The child path appended to the path in this instance.
	 * @param      array The array to check.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasValueByChildPath($path, array &$array)
	{
		$p = $this->pushRetNew($path);

		return $p->hasValue($array);
	}

	/**
	 * Returns the components of this path.
	 *
	 * @return     array The components
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getParts()
	{
		return $this->parts;
	}
}
?>
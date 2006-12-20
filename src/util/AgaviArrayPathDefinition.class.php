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
final class AgaviArrayPathDefinition
{
	/**
	 * constructor
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	private function __construct()
	{
	}

	/**
	 * Unsets a value at the given path.
	 *
	 * @param      array The path divided into its individual parts.
	 * @param      array The array we should operate on.
	 *
	 * @return     mixed The previosly stored value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function &unsetValue(array $parts, array &$array)
	{
		$a =& $array;

		$c = count($parts);
		for($i = 0; $i < $c; ++$i) {
			$part = $parts[$i];
			$last = ($i+1 == $c);
			if($part !== '' && $part !== null) {
				if(is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && isset($a[intval($part)])) {
					$part = intval($part);
				}
				if(isset($a[$part])) {
					if($last) {
						$oldValue =& $a[$part];
						unset($a[$part]);
						return $oldValue;
					} else {
						$a =& $a[$part];
					}
				} else {
					$retval = null;
					return $retval;
				}
			}
		}
	}


	/**
	 * Checks whether the array has a value at the given path.
	 *
	 * @param      array The path divided into its individual parts.
	 * @param      array The array we should operate on.
	 *
	 * @return     bool Whether the path exists in this array.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function hasValue(array $parts, array &$array)
	{
		$a = $array;

		foreach($parts as $part) {
			if($part !== '' && $part !== null) {
				if(is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && isset($a[intval($part)])) {
					$part = intval($part);
				}
				if(isset($a[$part])) {
					$a = $a[$part];
				} else {
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * Returns the value at the given path.
	 *
	 * @param      array The path divided into its individual parts.
	 * @param      array The array we should operate on.
	 * @param      mixed A default value if the path doesn't exist in the array.
	 *
	 * @return     mixed The value stored at the given path.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function &getValueFromArray(array $parts, array &$array, $default = null)
	{
		$a = &$array;

		foreach($parts as $part) {
			if($part !== '' && $part !== null) {
				if(is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && isset($a[intval($part)])) {
					$part = intval($part);
				}
				if(isset($a[$part]) && is_array($a)) {
					$a = &$a[$part];
				} else {
					//throw new AgaviException('The part: ' . $part . ' does not exist in the given array');
					return $default;
				}
			}
		}

		return $a;
	}

	/**
	 * Sets the value at the given path.
	 *
	 * @param      array The path divided into its individual parts.
	 * @param      array The array we should operate on.
	 * @param      mixed The value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function setValueFromArray(array $parts, array &$array, $value)
	{
		$a = &$array;

		foreach($parts as $part) {
			if($part !== '' && $part !== null) {
				if(is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && isset($a[intval($part)])) {
					$part = intval($part);
				}
				if(!isset($a[$part])) {
					$a[$part] = array();
				}
				$a = &$a[$part];
			}
		}

		$a = $value;
	}

	/**
	 * Returns an array with the single parts of the given path.
	 *
	 * @param      string The path.
	 *
	 * @return     array The parts of the given path.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function getPartsFromPath($path)
	{
		if(strlen($path) == 0) {
			return array('parts' => array(), 'absolute' => true);
		}

		$parts = array();
		$absolute = ($path[0] != '[');
		if(($pos = strpos($path, '[')) === false) {
			$parts[] = substr($path, 0, strlen($path));
		} else {
			if($absolute) {
				$parts[] = substr($path, 0, $pos);
			}

			$parts = array_merge($parts, explode('][', rtrim(ltrim(substr($path, $pos), '['), ']')));
		}

		return array('parts' => $parts, 'absolute' => $absolute);
	}


	/**
	 * Returns the flat key names of an array.
	 *
	 * This method calls itself recursivly to flatten the keys.
	 *
	 * @param      array The array which keys should be returned.
	 * @param      string The prefix for the name (only for internal use).
	 *
	 * @return     array The flattened keys.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function getFlatKeyNames(array $array, $prefix = '')
	{
		$names = array();
		foreach($array as $key => $value) {
			if(!$prefix) {
				// create the top node when no prefix was given
				$name = $key;
			} else {
				$name = $prefix . '[' . $key . ']';
			}

			if(is_array($value)) {
				$names = array_merge($names, AgaviArrayPathDefinition::getFlatKeyNames($value, $name));
			} else {
				$names[] = $name;
			}
		}
		return $names;
	}

}
?>
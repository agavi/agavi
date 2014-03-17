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
	 * Converts the given argument to an array of parts for use in the path getter/setters
	 * @param      array|string The path string or an array containing the path
	 *                          divided into its individual parts.
	 *
	 * @return     array        The array of parts.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.6
	 */
	protected static function preparePartsArray($partsArrayOrPathString)
	{
		if(is_array($partsArrayOrPathString)) {
			return $partsArrayOrPathString;
		} else {
			$partInfo = self::getPartsFromPath($partsArrayOrPathString);
			$parts = $partInfo['parts'];
			if(!$partInfo['absolute']) {
				// the value wasn't absolute, so an empty string is used for the first part
				array_unshift($parts, '');
			}
			return $parts;
		}
		
	}
	
	/**
	 * Unsets a value at the given path.
	 *
	 * @param      array|string The path string or an array containing the path
	 *                          divided into its individual parts.
	 * @param      array The array we should operate on.
	 *
	 * @return     mixed The previously stored value.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 */
	public static function &unsetValue($partsArrayOrPathString, array &$array)
	{
		$parts = self::preparePartsArray($partsArrayOrPathString);
		
		$a =& $array;

		$c = count($parts);
		for($i = 0; $i < $c; ++$i) {
			$part = $parts[$i];
			$last = ($i+1 == $c);
			if($part !== null) {
				if(is_array($a) && is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && (isset($a[(int)$part]) || array_key_exists((int)$part, $a))) {
					$part = (int)$part;
				}
				if(is_array($a) && (isset($a[$part]) || array_key_exists($part, $a))) {
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
		$retval = null;
		return $retval;
	}

	/**
	 * Checks whether the array has a value at the given path.
	 *
	 * @param      array|string The path string or an array containing the path
	 *                          divided into its individual parts.
	 * @param      array The array we should operate on.
	 *
	 * @return     bool Whether the path exists in this array.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 */
	public static function hasValue($partsArrayOrPathString, array &$array)
	{
		$parts = self::preparePartsArray($partsArrayOrPathString);
		
		$a = $array;

		foreach($parts as $part) {
			if($part !== null) {
				if(is_array($a) && is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && (isset($a[(int)$part]) || array_key_exists((int)$part, $a))) {
					$part = (int)$part;
				}
				if(is_array($a) && (isset($a[$part]) || array_key_exists($part, $a))) {
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
	 * @param      array|string The path string or an array containing the path
	 *                          divided into its individual parts.
	 * @param      array The array we should operate on.
	 * @param      mixed A default value if the path doesn't exist in the array.
	 *
	 * @return     mixed The value stored at the given path.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 */
	public static function &getValue($partsArrayOrPathString, array &$array, $default = null)
	{
		$parts = self::preparePartsArray($partsArrayOrPathString);
		
		$a = &$array;

		foreach($parts as $part) {
			if($part !== null) {
				if(is_array($a) && is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && (isset($a[(int)$part]) || array_key_exists((int)$part, $a))) {
					$part = (int)$part;
				}
				if(is_array($a) && (isset($a[$part]) || array_key_exists($part, $a))) {
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
	 * @param      array|string The path string or an array containing the path
	 *                          divided into its individual parts.
	 * @param      array The array we should operate on.
	 * @param      mixed The value.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 */
	public static function setValue($partsArrayOrPathString, array &$array, $value)
	{
		$parts = self::preparePartsArray($partsArrayOrPathString);
		
		$a = &$array;

		foreach($parts as $part) {
			if($part !== null) {
				if(is_array($a) && is_numeric($part) && strpos($part, '.') === false && strpos($part, ',') === false && (isset($a[(int)$part]) || array_key_exists((int)$part, $a))) {
					$part = (int)$part;
				}
				if(!isset($a[$part]) || !is_array($a[$part]) || (is_array($a) && !(isset($a[$part]) || array_key_exists($part, $a)))) {
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
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
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
			if(strpos($path, ']') !== false) {
				throw new InvalidArgumentException('Invalid "]" without opening "[" found');
			}
			$parts[] = $path;
		} else {
			$state = 0;
			$cur = '';
			foreach(str_split($path) as $c) {
				// this is the fastest way to loop over an string
				switch($state) {
					// the order is significant for performance
					case 2:
						// match all characters between []
						if($c == ']') {
							$parts[] = $cur;
							$cur = '';
							$state = 1;
						} elseif($c == '[') {
							throw new InvalidArgumentException('Invalid "[[" found');
						} else {
							$cur .= $c;
						}
						
						break;

					case 0:
						// match everything to the first '['
						if($c != '[') {
							$cur .= $c;
						} else {
							if($cur !== '') {
								$parts[] = $cur;
								$cur = '';
							}
							$state = 2;
						}
						break;

					case 1:
						// match exactly '['
						if($c == '[') {
							$state = 2;
						} else {
							throw new InvalidArgumentException('Invalid character after "]" found');
						}
						break;

				}
			}
			if($state == 0) {
				$parts[] = $cur;
			} elseif($state == 2) {
				throw new InvalidArgumentException('Missing "]" after opening "["');
			}
		}

		return array('parts' => $parts, 'absolute' => $absolute);
	}


	/**
	 * Returns the flat key names of an array.
	 *
	 * This method calls itself recursively to flatten the keys.
	 *
	 * @param      array The array which keys should be returned.
	 * @param      string The prefix for the name (only for internal use).
	 *
	 * @return     array The flattened keys.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 */
	public static function getFlatKeyNames(array $array, $prefix = null)
	{
		$names = array();
		foreach($array as $key => $value) {
			if($prefix === null) {
				// create the top node when no prefix was given
				if(strlen($key) == 0) {
					// when an empty key was used at top level, create a "relative" path, so the empty string doesn't get lost
					$name = '[' . $key . ']';
				} else {
					$name = $key;
				}
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
	
	/**
	 * Returns the flattened version of an array. So the returned array 
	 * will be one dimensional with the flattened key names as keys
	 * and their values from the original array as values.
	 *
	 * This method calls itself recursively to flatten the array.
	 *
	 * @param      array The array which should be flattened.
	 * @param      string The prefix for the key names (only for internal use).
	 *
	 * @return     array The flattened array.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public static function flatten($array, $prefix = null)
	{
		$flatArray = array();
		foreach($array as $key => $value) {
			if($prefix === null) {
				// create the top node when no prefix was given
				if(strlen($key) == 0) {
					// when an empty key was used at top level, create a "relative" path, so the empty string doesn't get lost
					$name = '[' . $key . ']';
				} else {
					$name = $key;
				}
			} else {
				$name = $prefix . '[' . $key . ']';
			}
			
			if(is_array($value)) {
				$flatArray += AgaviArrayPathDefinition::flatten($value, $name);
			} else {
				$flatArray[$name] = $value;
			}
		}
		return $flatArray;
	}
}
?>
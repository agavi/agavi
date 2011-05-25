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
 * AgaviConfig acts as global registry of agavi related configuration settings
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviConfig
{
	/**
	 * @var        array
	 */
	private static $config = array();

	/**
	 * @var        array
	 */
	private static $readonlies = array();

	/**
	 * Get a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     mixed The value of the directive, or null if not set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function get($name, $default = null)
	{
		if(isset(self::$config[$name]) || array_key_exists($name, self::$config)) {
			return self::$config[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Check if a configuration directive has been set.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool Whether the directive was set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function has($name)
	{
		return isset(self::$config[$name]) || array_key_exists($name, self::$config);
	}

	/**
	 * Check if a configuration directive has been set as read-only.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool Whether the directive is read-only.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function isReadonly($name)
	{
		return isset(self::$readonlies[$name]);
	}

	/**
	 * Set a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 * @param      mixed  The configuration value.
	 * @param      bool   Whether or not an existing value should be overwritten.
	 * @param      bool   Whether or not this value should be read-only once set.
	 *
	 * @return     bool   Whether or not the configuration directive has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function set($name, $value, $overwrite = true, $readonly = false)
	{
		$retval = false;
		if(($overwrite || !(isset(self::$config[$name]) || array_key_exists($name, self::$config))) && !isset(self::$readonlies[$name])) {
			self::$config[$name] = $value;
			if($readonly) {
				self::$readonlies[$name] = $value;
			}
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Remove a configuration value.
	 *
	 * @param      string The name of the configuration directive.
	 *
	 * @return     bool true, if removed successfully, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function remove($name)
	{
		$retval = false;
		if((isset(self::$config[$name]) || array_key_exists($name, self::$config)) && !isset(self::$readonlies[$name])) {
			unset(self::$config[$name]);
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Import a list of configuration directives.
	 *
	 * @param      array An array of configuration directives.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function fromArray(array $data)
	{
		// array_merge would reindex numeric keys, so we use the + operator
		// mind the operand order: keys that exist in the left one aren't overridden
		self::$config = self::$readonlies + $data + self::$config;
	}

	/**
	 * Get all configuration directives and values.
	 *
	 * @return     array An associative array of configuration values.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function toArray()
	{
		return self::$config;
	}

	/**
	 * Clear the configuration.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function clear()
	{
		$restore = array_intersect_assoc(self::$readonlies, self::$config);
		self::$config = $restore;
	}
}
?>
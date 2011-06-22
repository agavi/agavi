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
 * AgaviAttributeHolder provides a base class for managing attributes with
 * namespaces. It contains all the functionality AgaviParameterHolder provides.
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviAttributeHolder extends AgaviParameterHolder
{
	/**
	 * @var        array An array of attributes
	 */
	protected $attributes = array();

	/**
	 * @var        string The default attribute namespace
	 */
	protected $defaultNamespace = 'org.agavi';

	/**
	 * Get the default namespace name
	 *
	 * @return     string The default namespace name
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultNamespace()
	{
		return $this->defaultNamespace;
	}

	/**
	 * Clear all attributes.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function clearAttributes()
	{
		$this->attributes = array();
	}

	/**
	 * Retrieve an attribute.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.9.0
	 */
	public function &getAttribute($name, $ns = null, $default = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(isset($this->attributes[$ns])) {
			if(isset($this->attributes[$ns][$name]) || array_key_exists($name, $this->attributes[$ns])) {
				return $this->attributes[$ns][$name];
			}

			try {
				return AgaviArrayPathDefinition::getValue($name, $this->attributes[$ns], $default);
			} catch(InvalidArgumentException $e) {
				return $default;
			}
		}

		return $default;
	}

	/**
	 * Retrieve an array of attribute names.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An indexed array of attribute names, if the namespace
	 *                   exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getAttributeNames($ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(isset($this->attributes[$ns])) {
			return array_keys($this->attributes[$ns]);
		}
	}

	/**
	 * Retrieve an array of flattened attribute names. This means if an attribute
	 * is an array you wont get the name of the attribute in the result but 
	 * instead all child keys appended to the name (like foo[0],foo[1][0], ...)
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An indexed array of attribute names, if the namespace
	 *                   exists, otherwise null.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.3
	 */
	public function getFlatAttributeNames($ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(isset($this->attributes[$ns])) {
			return AgaviArrayPathDefinition::getFlatKeyNames($this->attributes[$ns]);
		}
	}

	/**
	 * Retrieve all attributes within a namespace.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getAttributes($ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		$retval = array();
		
		if(isset($this->attributes[$ns])) {
			$retval =& $this->attributes[$ns];
		}
		
		return $retval;
	}

	/**
	 * Retrieve all attributes within a namespace.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes if the namespace
	 *                   exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function &getAttributeNamespace($ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		$retval = null;

		if(isset($this->attributes[$ns])) {
			$retval =& $this->attributes[$ns];
		}

		return $retval;
	}

	/**
	 * Retrieve an array of attribute namespaces.
	 *
	 * @return     array An indexed array of attribute namespaces.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getAttributeNamespaces()
	{
		return array_keys($this->attributes);
	}

	/**
	 * Indicates whether or not an attribute exists.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 *
	 * @return     bool true, if the attribute exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasAttribute($name, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(isset($this->attributes[$ns])) {
			if(isset($this->attributes[$ns][$name]) || array_key_exists($name, $this->attributes[$ns])) {
				return true;
			}
			
			try {
				return AgaviArrayPathDefinition::hasValue($name, $this->attributes[$ns]);
			} catch(InvalidArgumentException $e) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Indicates whether or not an attribute namespace exists.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     bool true, if the namespace exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasAttributeNamespace($ns)
	{
		return isset($this->attributes[$ns]);
	}

	/**
	 * Remove an attribute.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 *
	 * @return     mixed An attribute value, if the attribute was removed,
	 *                   otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function &removeAttribute($name, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		$retval = null;

		if(isset($this->attributes[$ns])) {
			if(isset($this->attributes[$ns][$name]) || array_key_exists($name, $this->attributes[$ns])) {
				$retval =& $this->attributes[$ns][$name];
				unset($this->attributes[$ns][$name]);
			} else {
				try {
					$retval =& AgaviArrayPathDefinition::unsetValue($name, $this->attributes[$ns]);
				} catch(InvalidArgumentException $e) {
				}
			}
		}

		return $retval;
	}

	/**
	 * Remove an attribute namespace and all of its associated attributes.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     mixed An array with all namespace attributes, if the namespace
	 *                   was removed, or null otherwise.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function removeAttributeNamespace($ns)
	{
		$retval = null;
		
		if(isset($this->attributes[$ns])) {
			$retval =& $this->attributes[$ns];
			
			unset($this->attributes[$ns]);
		}
		
		return $retval;
	}

	/**
	 * Set an attribute.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAttribute($name, $value, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		$this->attributes[$ns][$name] = $value;
	}

	/**
	 * Append an attribute.
	 *
	 * If an attribute with the name already exists, it will be converted to an
	 * array and the new value appended.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function appendAttribute($name, $value, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		if(!isset($this->attributes[$ns][$name]) || !is_array($this->attributes[$ns][$name])) {
			settype($this->attributes[$ns][$name], 'array');
		}
		
		$this->attributes[$ns][$name][] = $value;
	}

	/**
	 * Set an attribute by reference.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAttributeByRef($name, &$value, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		$this->attributes[$ns][$name] =& $value;
	}

	/**
	 * Append an attribute by reference.
	 *
	 * If an attribute with the name already exists, it will be converted to an
	 * array and the reference to the new value appended.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function appendAttributeByRef($name, &$value, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		if(!isset($this->attributes[$ns][$name]) || !is_array($this->attributes[$ns][$name])) {
			settype($this->attributes[$ns][$name], 'array');
		}
		
		$this->attributes[$ns][$name][] =& $value;
	}

	/**
	 * Set an array of attributes.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array  An associative array of attributes and their
	 *                    associated values.
	 * @param      string An attribute namespace.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAttributes(array $attributes, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		// array_merge would reindex numeric keys, so we use the + operator
		// mind the operand order: keys that exist in the left one aren't overridden
		$this->attributes[$ns] = $attributes + $this->attributes[$ns];
	}

	/**
	 * Set an array of attributes by reference.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array  An associative array of attributes and references to
	 *                    their associated values.
	 * @param      string An attribute namespace.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAttributesByRef(array &$attributes, $ns = null)
	{
		if($ns === null) {
			$ns = $this->defaultNamespace;
		}

		if(!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		foreach($attributes as $key => &$value) {
			$this->attributes[$ns][$key] =& $value;
		}
	}
}

?>
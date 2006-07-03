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
 * AgaviConfigValueHolder is the storage class for the AgaviXmlConfigHandler
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
	class AgaviConfigValueHolder implements ArrayAccess, IteratorAggregate
	{
		/**
		 * @var        string The name of this value.
		 */
		protected $__name = '';
		/**
		 * @var        array The attributes of this value.
		 */
		protected $__attributes = array();
		/**
		 * @var        array The child nodes of this value.
		 */
		protected $__childs = array();
		/**
		 * @var        string The value.
		 */
		protected $__value = null;

		/**
		 * Sets the name of this value.
		 *
		 * @param      string The name.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function setName($name)
		{
			$this->__name = $name;
		}

		/**
		 * Returns the name of this value.
		 *
		 * @return     string The name.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getName()
		{
			return $this->__name;
		}

		/**
		 * Adds a named children to this value.
		 *
		 * @param      string The name of the child.
		 * @param      AgaviConfigValueHolder The child value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function addChildren($name, $children)
		{
			$this->$name = $children;
			$this->__childs[$name] = $children;
		}

		/**
		 * Adds a unnamed children to this value.
		 *
		 * @param      AgaviConfigValueHolder The child value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function appendChildren($children)
		{
			$this->__childs[] = $children;
		}

		/**
		 * Checks whether the value has children at all (no params) or whether a
		 * child with the given name exists.
		 *
		 * @param      string The name of the child.
		 *
		 * @return     bool True if children exist, false if not.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function hasChildren($child = null)
		{
			if($child === null)
				return count($this->__childs) > 0;

			return isset($this->__childs[$child]);
		}

		/**
		 * Returns the children of this value.
		 *
		 * @return     array An array with the childs of this value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getChildren()
		{
			return $this->__childs;
		}

		/**
		 * Set an attribute.
		 *
		 * If an attribute with the name already exists the value will be
		 * overridden.
		 *
		 * @param      string An attribute name.
		 * @param      mixed  An attribute value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function setAttribute($name, $value)
		{
			$this->__attributes[$name] = $value;
		}

		/**
		 * Indicates whether or not an attribute exists.
		 *
		 * @param      string An attribute name.
		 *
		 * @return     bool true, if the attribute exists, otherwise false.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function hasAttribute($name)
		{
			return isset($this->__attributes[$name]);
		}

		/**
		 * Retrieve an attribute.
		 *
		 * @param      string An attribute name.
		 * @param      mixed  A default attribute value.
		 *
		 * @return     mixed An attribute value, if the attribute exists, otherwise
		 *                   null or the given default.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getAttribute($name, $default = null)
		{
			return isset($this->__attributes[$name]) ? $this->__attributes[$name] : $default;
		}

		/**
		 * Retrieve all attributes.
		 *
		 * @return     array An associative array of attributes.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getAttributes()
		{
			return $this->__attributes;
		}

		/**
		 * Set the value of this value node.
		 *
		 * @param      string A value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function setValue($value)
		{
			$this->__value = $value;
		}

		/**
		 * Retrieves the value of this value node.
		 *
		 * @return     string The value of this node.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getValue()
		{
			return $this->__value;
		}

		/**
		 * Retrieves the info of this value node.
		 *
		 * @return     array An array containing the info for this node.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getNode()
		{
			return array(
				'name' => $this->__name,
				'attributes' => $this->__attributes,
				'children' => $this->__childs,
				'value' => $this->__value,
			);
		}

		/**
		 * Determines if a named child exists. From ArrayAccess.
		 *
		 * @param      string Offset to check
		 *
		 * @return     bool Whether the offset exists.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function offsetExists($offset)
		{
			return isset($this->__childs[$offset]);
		}

		/**
		 * Retrieves a named child. From ArrayAccess.
		 *
		 * @param      string Offset to retrieve
		 *
		 * @return     AgaviConfigValueHolder The child value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function offsetGet($offset)
		{
			if(!isset($this->__childs[$offset]))
				return null;
			return $this->__childs[$offset];
		}

		/**
		 * Inserts a named child. From ArrayAccess.
		 *
		 * @param      string Offset to modify
		 * @param      AgaviConfigValueHolder The child value.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function offsetSet($offset, $value)
		{
			$this->__childs[$offset] = $value;
		}

		/**
		 * Deletes a named child. From ArrayAccess.
		 *
		 * @return     string Offset to delete.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function offsetUnset($offset)
		{
			unset($this->__childs[$offset]);
		}

		/**
		 * Returns an Iterator for the child nodes. From IteratorAggregate.
		 *
		 * @return     Iterator The iterator.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function getIterator()
		{
			return new ArrayIterator($this->getChildren());
		}

		/**
		 * Retrieves the string representation of this value node. This is 
		 * currently only the value of the node.
		 *
		 * @return     string The string representation.
		 *
		 * @author     Dominik del Bondio <ddb@bitxtender.com>
		 * @since      0.11.0
		 */
		public function __toString()
		{
			return $this->__value;
		}
	}

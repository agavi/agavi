<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @deprecated Not used anymore by XML config handlers, to be removed in Agavi 1.1
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviConfigValueHolder implements ArrayAccess, IteratorAggregate
{
	/**
	 * @var        string The name of this value.
	 */
	protected $_name = '';
	/**
	 * @var        array The attributes of this value.
	 */
	protected $_attributes = array();
	/**
	 * @var        array The child nodes of this value.
	 */
	protected $_childs = array();
	/**
	 * @var        string The value.
	 */
	protected $_value = null;

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
		$this->_name = $name;
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
		return $this->_name;
	}

	/**
	 * isset() overload.
	 *
	 * @param      string Name of the child.
	 *
	 * @return     bool Whether or not that child exists.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __isset($name)
	{
		return $this->hasChildren($name);
	}

	/**
	 * Magic getter overload.
	 *
	 * @param      string Name of the child .
	 *
	 * @return     AgaviConfigValueHolder The child, if it exists.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __get($name)
	{
		if(isset($this->_childs[$name])) {
			return $this->_childs[$name];
		} else {
			$tagName = $name;
			$tagNameStart = '';
			if(($lastUScore = strrpos($tagName, '_')) !== false) {
				$lastUScore++;
				$tagNameStart = substr($tagName, 0, $lastUScore);
				$tagName = substr($tagName, $lastUScore);
			}

			// check if the requested node was specified using the plural version
			// and create a "virtual" node which reflects the non existant plural node
			$singularName = $tagNameStart . AgaviInflector::singularize($tagName);
			if($this->hasChildren($singularName)) {

				$vh = new AgaviConfigValueHolder();
				$vh->setName($name);

				foreach($this->_childs as $child) {
					if($child->getName() == $singularName) {
						$vh->addChildren($singularName, $child);
					}
				}

				return $vh;
			} else {
				//throw new AgaviException('Node with the name ' . $name . ' does not exist ('.$this->getName().', '.implode(', ', array_keys($this->_childs)).')');
				return null;
			}
		}
	}

	/**
	 * Adds a named children to this value. If a children with the same name
	 * already exists the given value will be appended to the children.
	 *
	 * @param      string The name of the child.
	 * @param      AgaviConfigValueHolder The child value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function addChildren($name, $children)
	{
		if(!$this->hasChildren($name)) {
			$this->$name = $children;
			$this->_childs[$name] = $children;
		} else {
			$this->appendChildren($children);
		}
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
		$this->_childs[] = $children;
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
		if($child === null) {
			return count($this->_childs) > 0;
		}

		if(isset($this->_childs[$child])) {
			return true;
		} else {
			$tagName = $child;
			$tagNameStart = '';
			if(($lastUScore = strrpos($tagName, '_')) !== false) {
				$lastUScore++;
				$tagNameStart = substr($tagName, 0, $lastUScore);
				$tagName = substr($tagName, $lastUScore);
			}

			$singularName = $tagNameStart . AgaviInflector::singularize($tagName);
			return isset($this->_childs[$singularName]);
		}
	}

	/**
	 * Returns the children of this value.
	 *
	 * @param      string Return only the childs matching this node (tag) name.
	 *
	 * @return     array An array with the childs of this value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getChildren($nodename = null)
	{
		if($nodename === null) {
			return $this->_childs;
		} else {
			$childs = array();
			foreach($this->_childs as $child) {
				if($child->getName() == $nodename) {
					$childs[] = $child;
				}
			}

			return $childs;
		}
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
		$this->_attributes[$name] = $value;
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
		return isset($this->_attributes[$name]);
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
		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : $default;
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
		return $this->_attributes;
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
		$this->_value = $value;
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
		return $this->_value;
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
			'name' => $this->_name,
			'attributes' => $this->_attributes,
			'children' => $this->_childs,
			'value' => $this->_value,
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
		return isset($this->_childs[$offset]);
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
		if(!isset($this->_childs[$offset]))
			return null;
		return $this->_childs[$offset];
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
		$this->_childs[$offset] = $value;
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
		unset($this->_childs[$offset]);
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
		return $this->_value;
	}
}

?>
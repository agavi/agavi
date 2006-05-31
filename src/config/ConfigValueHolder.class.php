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
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */

	class AgaviConfigValueHolder implements ArrayAccess, IteratorAggregate
	{
		protected $__name = '';
		protected $__attributes = array();
		protected $__childs = array();
		protected $__value = null;

		public function setName($name)
		{
			$this->__name = $name;
		}

		public function getName()
		{
			return $this->__name;
		}

		public function addChildren($name, $children)
		{
			$this->$name = $children;
			$this->__childs[$name] = $children;
		}

		public function appendChildren($children)
		{
			$this->__childs[] = $children;
		}

		public function hasChildren($child = null)
		{
			if($child === null)
				return count($this->__childs) > 0;

			return isset($this->__childs[$child]);
		}

		public function getChildren()
		{
			return $this->__childs;
		}

		public function setAttribute($name, $value)
		{
			$this->__attributes[$name] = $value;
		}

		public function hasAttribute($name)
		{
			return isset($this->__attributes[$name]);
		}

		public function getAttribute($name, $default = null)
		{

			return isset($this->__attributes[$name]) ? $this->__attributes[$name] : $default;
		}

		public function getAttributes()
		{
			return $this->__attributes;
		}

		public function setValue($value)
		{
			$this->__value = $value;
		}

		public function getValue()
		{
			return $this->__value;
		}

		public function getNode()
		{
			return array(
				'attributes' => $this->__attributes,
				'children' => $this->__childs,
				'value' => $this->__value,
			);
		}

		public function offsetExists($offset)
		{
			return isset($this->__childs[$offset]);
		}

		public function offsetGet($offset)
		{
			if(!isset($this->__childs[$offset]))
				return null;
			return $this->__childs[$offset];
		}

		public function offsetSet($offset, $value)
		{
			$this->__childs[$offset] = $value;
		}

		public function offsetUnset($offset)
		{
			unset($this->__childs[$offset]);
		}

		public function getIterator()
		{
			return new ArrayIterator($this->getChildren());
		}

		public function __toString()
		{
			return $this->__value;
			return $this->getValue();
		}
	}

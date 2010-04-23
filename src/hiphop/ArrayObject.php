<?php

class ArrayObject implements IteratorAggregate, Traversable, ArrayAccess, Serializable, Countable
{
	/* Constants */
	const STD_PROP_LIST = 1;
	const ARRAY_AS_PROPS = 2;

	/* Methods */
	public function __construct ($input, $flags = null, $iterator_class = null) {
	}

	public function append($value)
	{
	}

	public function asort()
	{
	}

	public function count()
	{
		return 0;
	}

	public function exchangeArray($input)
	{
	}

	public function getArrayCopy()
	{
	}

	public function getFlags()
	{
	}

	public function getIterator()
	{
	}

	public function getIteratorClass()
	{
		return 0;
	}

	public function ksort()
	{
	}

	public function natcasesort()
	{
	}

	public function natsort()
	{
	}

	public function offsetExists($index)
	{
	}

	public function offsetGet($index)
	{
	}

	public function offsetSet($index, $newval)
	{
	}

	public function offsetUnset($index)
	{
	}

	public function serialize()
	{
	}

	public function setFlags($flags)
	{
	}

	public function setIteratorClass($iterator_class)
	{
	}

	public function uasort($cmp_function)
	{
	}

	public function uksort($cmp_function)
	{
	}

	public function unserialize($serialized)
	{
	}
}

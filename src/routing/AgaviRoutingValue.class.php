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
 * 
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviRoutingValue implements ArrayAccess
{
	protected $value;
	protected $prefix;
	protected $postfix;
	protected $valueEncoded = false;
	protected $prefixEncoded = true;
	protected $postfixEncoded = true;
	
	protected static $arrayMap = array(
		'pre'  => 'prefix',
		'val'  => 'value',
		'post' => 'postfix',
	);
	
	public function __construct($value, $prefix = null, $postfix = null, $valueEncoded = false, $prefixEncoded = true, $postfixEncoded = true)
	{
		$this->value = $value;
		$this->prefix = $prefix;
		$this->postfix = $postfix;
		$this->valueEncoded = $valueEncoded;
		$this->prefixEncoded = $prefixEncoded;
		$this->postfixEncoded = $postfixEncoded;
	}
	
	public function setValue($value, $isEncoded = null)
	{
		$this->value = $value;
		if($isEncoded !== null) {
			$this->valueEncoded = $isEncoded;
		}
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function setPrefix($value, $isEncoded = null)
	{
		$this->prefix = $value;
		if($isEncoded !== null) {
			$this->prefixEncoded = $isEncoded;
		}
	}
	
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	public function setPostfix($value, $isEncoded = null)
	{
		$this->postfix = $value;
		if($isEncoded !== null) {
			$this->postfixEncoded = $isEncoded;
		}
	}
	
	public function getPostfix()
	{
		return $this->postfix;
	}
	
	public function isValueEncoded()
	{
		return $this->valueEncoded;
	}
	
	public function isPrefixEncoded()
	{
		return $this->prefixEncoded;
	}
	
	public function isPostfixEncoded()
	{
		return $this->postfixEncoded;
	}
	
	// TODO: naming
	public function hasPrefixOrPostfix()
	{
		return $this->prefix || $this->postfix;
	}
	
	public function equals($other)
	{
		if($other instanceof self) {
			return $this == $other;
		} elseif(is_array($other)) {
			return $this->value == $other['val'] && $this->prefix == $other['pre'] && $this->postfix == $other['post'] && !$this->valueEncoded && $this->prefixEncoded && $this->postfixEncoded;
		} else {
			return $this->prefix === null && $this->postfix === null && $this->value == $other && !$this->valueEncoded;
		}
	}
	
	public function offsetExists($offset)
	{
		return isset(self::$arrayMap[$offset]);
	}
	
	public function offsetGet($offset)
	{
		if(isset(self::$arrayMap[$offset])) {
			return $this->{self::$arrayMap[$offset]};
		}
	}
	
	public function offsetSet($offset, $value)
	{
		if(isset(self::$arrayMap[$offset])) {
			$this->{self::$arrayMap[$offset]} = $value;
		}
	}
	
	public function offsetUnset($offset)
	{
		if(isset(self::$arrayMap[$offset])) {
			$this->{self::$arrayMap[$offset]} = null;
		}
	}
	
	public function __toString()
	{
		return $this->prefix . $this->value . $this->postfix;
	}
	
	public static function __set_state(array $data)
	{
		return new self($data['value'], $data['prefix'], $data['postfix'], $data['valueEncoded'], $data['prefixEncoded'], $data['postfixEncoded']);
	}
}

?>
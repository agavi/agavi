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
	protected $context;
	
	protected $value;
	protected $prefix;
	protected $postfix;
	protected $valueNeedsEncoding = true;
	protected $prefixNeedsEncoding = false;
	protected $postfixNeedsEncoding = false;
	
	protected static $arrayMap = array(
		'pre'  => 'prefix',
		'val'  => 'value',
		'post' => 'postfix',
	);
	
	public function __construct($value, $prefix = null, $postfix = null, $valueNeedsEncoding = true, $prefixNeedsEncoding = false, $postfixNeedsEncoding = false)
	{
		$this->value = $value;
		$this->prefix = $prefix;
		$this->postfix = $postfix;
		$this->valueNeedsEncoding = $valueNeedsEncoding;
		$this->prefixNeedsEncoding = $prefixNeedsEncoding;
		$this->postfixNeedsEncoding = $postfixNeedsEncoding;
	}
	
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
	}
	
	public function setValue($value, $needsEncoding = true)
	{
		$this->value = $value;
		$this->valueNeedsEncoding = $needsEncoding;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function setPrefix($value, $needsEncoding = false)
	{
		$this->prefix = $value;
		$this->prefixNeedsEncoding = $needsEncoding;
	}
	
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	public function setPostfix($value, $needsEncoding = false)
	{
		$this->postfix = $value;
		$this->postfixNeedsEncoding = $needsEncoding;
	}
	
	public function getPostfix()
	{
		return $this->postfix;
	}
	
	public function getValueNeedsEncoding()
	{
		return $this->valueNeedsEncoding;
	}
	
	public function getPrefixNeedsEncoding()
	{
		return $this->prefixNeedsEncoding;
	}
	
	public function getPostfixNeedsEncoding()
	{
		return $this->postfixNeedsEncoding;
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
		$ro = $this->context->getRouting();
		return sprintf('%s%s%s', 
			$this->prefixNeedsEncoding ? $ro->escapeOutputParameter($this->prefix) : $this->prefix,
			$this->valueNeedsEncoding ? $ro->escapeOutputParameter($this->value) : $this->value,
			$this->postfixNeedsEncoding ? $ro->escapeOutputParameter($this->postfix) : $this->postfix
		);
	}
	
	public static function __set_state(array $data)
	{
		return new self($data['value'], $data['prefix'], $data['postfix'], $data['valueNeedsEncoding'], $data['prefixNeedsEncoding'], $data['postfixNeedsEncoding']);
	}
}

?>
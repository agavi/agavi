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
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
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
	
	/**
	 * Constructor
	 * 
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct($value, $valueNeedsEncoding = true)
	{
		$this->value = $value;
		$this->valueNeedsEncoding = $valueNeedsEncoding;
	}
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context instead of the instance, which will later
	 * be restored by __wakeup().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function __sleep()
	{
		$this->contextName = $this->context->getName();
		$arr = get_object_vars($this);
		unset($arr['context']);
		return array_keys($arr);
	}

	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context instance based on their names set by __sleep().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function __wakeup()
	{
		$this->context = AgaviContext::getInstance($this->contextName);
		
		unset($this->contextName);
	}
	
	/**
	 * Initialize the routing value.
	 *
	 * @param      AgaviContext The Context.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
	}
	
	/**
	 * Sets the value.
	 * 
	 * @param      mixed  The value
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	/**
	 * Retrieves the value.
	 * 
	 * @param      mixed
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * Sets the prefix.
	 * 
	 * @param      string The prefix
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setPrefix($value)
	{
		$this->prefix = $value;
		return $this;
	}
	
	/**
	 * Retrieve the prefix.
	 * 
	 * @return     string
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	/**
	 * Checks if an prefix was set.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasPrefix()
	{
		return $this->prefix !== null;
	}
	
	/**
	 * Set the postfix.
	 * 
	 * @param      string The postfix
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setPostfix($value)
	{
		$this->postfix = $value;
		return $this;
	}
	
	/**
	 * Retrieve the postfix.
	 * 
	 * @return     string
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPostfix()
	{
		return $this->postfix;
	}
	
	/**
	 * Checks if an postfix was set.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasPostfix()
	{
		return $this->postfix !== null;
	}
	
	/**
	 * Sets whether the value needs to be encoded or not.
	 * 
	 * @param      bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function setValueNeedsEncoding($needsEncoding)
	{
		$this->valueNeedsEncoding = $needsEncoding;
		return $this;
	}
	
	/**
	 * Retrieves whether the value needs to be encoded.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getValueNeedsEncoding()
	{
		return $this->valueNeedsEncoding;
	}
	
	/**
	 * Sets whether the prefix needs to be encoded or not.
	 * 
	 * @param      bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function setPrefixNeedsEncoding($needsEncoding)
	{
		$this->prefixNeedsEncoding = $needsEncoding;
		return $this;
	}
	
	/**
	 * Retrieves whether the prefix needs to be encoded.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPrefixNeedsEncoding()
	{
		return $this->prefixNeedsEncoding;
	}
	
	/**
	 * Sets whether the postfix needs to be encoded or not.
	 * 
	 * @param      bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function setPostfixNeedsEncoding($needsEncoding)
	{
		$this->postfixNeedsEncoding = $needsEncoding;
		return $this;
	}
	
	/**
	 * Retrieves whether the postfix needs to be encoded.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPostfixNeedsEncoding()
	{
		return $this->postfixNeedsEncoding;
	}
	
	/**
	 * Checks if this routing value is equal to the given parameter.
	 * 
	 * @param      mixed The value to compare $this against
	 * 
	 * @return     bool Whether the value matches $this
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
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
	
	/**
	 * ArrayAccess method
	 * 
	 * @param      mixed The offset
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function offsetExists($offset)
	{
		return isset(self::$arrayMap[$offset]);
	}
	
	/**
	 * ArrayAccess method
	 * 
	 * @param      mixed The offset
	 * 
	 * @return     mixed The value
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function offsetGet($offset)
	{
		if(isset(self::$arrayMap[$offset])) {
			return $this->{self::$arrayMap[$offset]};
		}
	}
	
	/**
	 * ArrayAccess method
	 * 
	 * @param      mixed The offset
	 * @param      mixed The value
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function offsetSet($offset, $value)
	{
		if(isset(self::$arrayMap[$offset])) {
			$this->{self::$arrayMap[$offset]} = $value;
		}
	}
	
	/**
	 * ArrayAccess method
	 * 
	 * @param      mixed The offset
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function offsetUnset($offset)
	{
		if(isset(self::$arrayMap[$offset])) {
			$this->{self::$arrayMap[$offset]} = null;
		}
	}
	
	/**
	 * Returns the encoded value (without pre- or postfix)
	 * 
	 * @return     string
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __toString()
	{
		return $this->valueNeedsEncoding ? $this->context->getRouting()->escapeOutputParameter($this->value) : $this->value;
	}
}

?>
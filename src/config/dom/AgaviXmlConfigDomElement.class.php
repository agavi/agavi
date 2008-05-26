<?php

class AgaviXmlConfigDomElement extends DOMElement implements IteratorAggregate
{
	/**
	 * Overloaded method for accessing child nodes. Does the pluralizing etc, and
	 * provides convenient access through a potentially set default namespace.
	 *
	 * @param      string The child element name.
	 *
	 * @return     mixed A DOMNodeList or an AgaviXmlConfigDomElement.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function __get($name) {
		// TODO: add {namespace}element support
		// should look into the default ns, IMO. otherwise, you gotta use getChild()
		// must use singular/plural handling
	}
	
	public function __isset($name) {
		// TODO: add {namespace}element support
		// should look into the default ns, IMO. otherwise, you gotta use hasChild()
	}
	
	public function __toString()
	{
		return $this->getValue();
	}
	
	public function getName()
	{
		// what to return here? name with prefix? no.
		// but... element name, or with ns prefix?
	}
	
	public function getValue()
	{
		// TODO: or textContent?
		// trimmed or not? in utf-8 or native encoding?
		// I'd really say we only support utf-8 for the new api
		return $this->nodeValue;
	}
	
	/**
	 * Returns an iterator for the child nodes.
	 *
	 * @return     Iterator An iterator.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getIterator()
	{
		// should only pull elements from the default ns
		// remember to handle special case where we are the document element and an agavi config - must find <configuration> elements from the envelope ns here
		return $this->ownerDocument->getXpath()->query('child::element()', $this);
	}
	
	public function hasChildren($defaultNamespaceOnly = false)
	{
		// check for child elements(!) using XPath
		// if arg is true, then only check for elements from our default namespace
		// remember to handle special case where we are the document element and an agavi config - must find <configuration> elements from the envelope ns here
	}
	
	public function getChildren($defaultNamespaceOnly = false)
	{
		// check for child elements(!) using XPath
		// if arg is true, then only check for elements from our default namespace
		// remember to handle special case where we are the document element and an agavi config - must find <configuration> elements from the envelope ns here
	}
	
	public function hasChild($name, $namespaceUri = null)
	{
		// if namespace uri is null, use default ns. if empty string, use no ns
		// remember to handle special case where we are the document element and an agavi config - must find <configuration> elements from the envelope ns here
		// remember singular/plural support
	}
	
	public function getChild($name, $namespaceUri = null)
	{
		// if namespace uri is null, use default ns. if empty string, use no ns
		// remember to handle special case where we are the document element and an agavi config - must find <configuration> elements from the envelope ns here
	}
	
	/**
	 * Retrieve an attribute value.
	 * Unlike DOMElement::getAttribute(), this method accepts an optional default
	 * return value.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null or the given default.
	 *
	 * @see        DOMElement::getAttribute()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getAttribute($name, $default = null)
	{
		$retval = parent::getAttribute($name);
		
		if($retval === null) {
			$retval = $default;
		}
		
		return $retval;
	}
	
	/**
	 * Retrieve a namespaced attribute value.
	 * Unlike DOMElement::getAttributeNS(), this method accepts an optional
	 * default return value.
	 *
	 * @param      string A namespace URI.
	 * @param      string An attribute name.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null or the given default.
	 *
	 * @see        DOMElement::getAttributeNS()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getAttributeNS($namespaceUri, $localName, $default = null)
	{
		$retval = parent::getAttributeNS($namespaceUri, $localName);
		
		if($retval === null) {
			$retval = $default;
		}
		
		return $retval;
	}
	
	public function getAgaviParameters()
	{
		return $this->ownerDocument->xpath->query('child::aens:parameters | child::aens:parameter');
	}
}

?>
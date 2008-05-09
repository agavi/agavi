<?php

class AgaviXmlConfigDomElement extends DOMElement
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
	}
	
	public function __isset($name) {
		// TODO: add {namespace}element support
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
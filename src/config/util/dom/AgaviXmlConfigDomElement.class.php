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
		return $this->nodeName;
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
		$prefix = $this->ownerDocument->getDefaultNamespacePrefix();
		if($prefix) {
			return $this->ownerDocument->getXpath()->query(sprintf('child::%s:element()', $prefix), $this);
		} else {
			return $this->ownerDocument->getXpath()->query('child::element()', $this);
		}
	}
	
	protected function singularize($name)
	{
		$names = preg_split('#([_\-\.])#', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
		$names[count($names) - 1] = AgaviInflector::singularize(end($names));
		return implode('', $names);
	}
	
	public function countChildren($name, $namespaceUri = null)
	{
		// check for child elements(!) using XPath
		// if arg is true, then only check for elements from our default namespace
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		$singularName = $this->singularize($name);
		
		$xpath = $this->ownerDocument->getXpath();
		if($namespaceUri) {
			return (int)$xpath->evaluate(sprintf('count(child::*[local-name() = "%2$s" and namespace-uri() = "%3$s"]) + count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s"])', $name, $singularName, $namespaceUri), $this);
		} else {
			return (int)$xpath->evaluate(sprintf('count(%2$s) + count(%1$s/%2$s)', $name, $singularName), $this);
		}
	}
	
	public function hasChildren($name, $namespaceUri = null)
	{
		return $this->countChildren($name, $namespaceUri) !== 0;
	}
	
	public function getChildren($name, $namespaceUri = null)
	{
		// check for child elements(!) using XPath
		// if arg is true, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		$singularName = $this->singularize($name);
		
		$xpath = $this->ownerDocument->getXpath();
		if($namespaceUri) {
			return $xpath->query(sprintf('child::*[local-name() = "%2$s" and namespace-uri() = "%3$s"] | child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s"]', $name, $singularName, $namespaceUri), $this);
		} else {
			return $xpath->query(sprintf('%1$s/%2$s | %2$s', $name, $singularName), $this);
		}
	}
	
	public function hasChild($name, $namespaceUri = null)
	{
		// if namespace uri is null, use default ns. if empty string, use no ns
		return $this->countChildren($name) === 1;
		
		// XXX: not necessary for single elements?
		// remember singular/plural support
	}
	
	/**
	 * Returns a single child element with a given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     DOMElement The child element, or null if none xists.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getChild($name, $namespaceUri = null)
	{
		$list = $this->getChildren($name, $namespaceUri);
		
		if($list->length > 0) {
			return $list->item(0);
		}
		return null;
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
	
	public function getAgaviParameters(array $existing = array())
	{
		if($this->ownerDocument->isAgaviConfiguration()) {
			$elements = $this->getChildren('parameters', AgaviXmlConfigParser::AGAVI_ENVELOPE_NAMESPACE_LATEST);
			$result = array();
			
			foreach($elements as $element) {
				if($element->hasChildren('parameters', AgaviXmlConfigParser::AGAVI_ENVELOPE_NAMESPACE_LATEST)) {
					$result[$element->getAttribute('name')] = $element->getAgaviParameters();
				} else {
					$result[$element->getAttribute('name')] = $element->getValue();
				}
			}
			
			return array_merge($existing, $result);
		} else {
			return $existing;
		}
	}
}

?>
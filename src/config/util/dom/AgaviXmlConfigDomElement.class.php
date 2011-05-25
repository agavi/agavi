<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * Extended DOMElement class with several convenience enhancements.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviXmlConfigDomElement extends DOMElement implements IteratorAggregate
{
	/**
	 * __toString() magic method, returns the element value.
	 *
	 * @see        AgaviXmlConfigDomElement::getValue()
	 *
	 * @return     string The element value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function __toString()
	{
		return $this->getValue();
	}
	
	/**
	 * Returns the element name.
	 *
	 * @return     string The element name.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getName()
	{
		// what to return here? name with prefix? no.
		// but... element name, or with ns prefix?
		return $this->nodeName;
	}
	
	/**
	 * Returns the element value.
	 *
	 * @return     string The element value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIterator()
	{
		// should only pull elements from the default ns
		$prefix = $this->ownerDocument->getDefaultNamespacePrefix();
		if($prefix) {
			return $this->ownerDocument->getXpath()->query(sprintf('child::%s:*', $prefix), $this);
		} else {
			return $this->ownerDocument->getXpath()->query('child::*', $this);
		}
	}
	
	/**
	 * Retrieve singular form of given element name.
	 * This does special splitting only of the last part of the name if the name
	 * of the element contains hyphens, underscores or dots.
	 *
	 * @param      string The element name to singularize.
	 *
	 * @return     string The singularized element name.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	protected function singularize($name)
	{
		// TODO: shouldn't this be static?
		$names = preg_split('#([_\-\.])#', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
		$names[count($names) - 1] = AgaviInflector::singularize(end($names));
		return implode('', $names);
	}
	
	/**
	 * Convenience method to retrieve child elements of the given name.
	 * Accepts singular or plural forms of the name, and will detect and handle
	 * parent containers with plural names properly.
	 *
	 * @param      string The name of the element(s) to check for.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     DOMNodeList A list of the child elements.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function get($name, $namespaceUri = null)
	{
		return $this->getChildren($name, $namespaceUri, true);
	}
	
	/**
	 * Convenience method to check if there are child elements of the given name.
	 * Accepts singular or plural forms of the name, and will detect and handle
	 * parent containers with plural names properly.
	 *
	 * @param      string The name of the element(s) to check for.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     bool True if one or more child elements with the given name
	 *                  exist, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function has($name, $namespaceUri = null)
	{
		return $this->hasChildren($name, $namespaceUri, true);
	}
	
	/**
	 * Count the number of child elements with a given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     int The number of child elements with the given name.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function countChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// init our vars
		$query = '';
		$singularName = null;
		
		// tag our element, because older libxmls will mess things up otherwise
		// http://trac.agavi.org/ticket/1039
		$marker = uniqid('', true);
		$this->setAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker', $marker);
		
		if($pluralMagic) {
			// we always assume that we either get plural names, or the singular of the singular is not different from the singular :)
			$singularName = $this->singularize($name);
			if($namespaceUri) {
				$query = 'count(child::*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"]) + count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../../@agavi_annotations_latest:marker = "%4$s"])';
			} else {
				$query = 'count(%1$s[../@agavi_annotations_latest:marker = "%4$s"]/%2$s[../../@agavi_annotations_latest:marker = "%4$s"]) + count(%2$s[../@agavi_annotations_latest:marker = "%4$s"])';
			}
		} else {
			if($namespaceUri) {
				$query = 'count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"])';
			} else {
				$query = 'count(%1$s[../@agavi_annotations_latest:marker = "%4$s"])';
			}
		}
		
		$retval = (int)$this->ownerDocument->getXpath()->evaluate(sprintf($query, $name, $singularName, $namespaceUri, $marker), $this);
		
		$this->removeAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker');
		
		return $retval;
	}
	
	/**
	 * Determine whether there is at least one instance of a child element with a
	 * given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     bool True if one or more child elements with the given name
	 *                  exist, false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		return $this->countChildren($name, $namespaceUri, $pluralMagic) !== 0;
	}
	
	/**
	 * Retrieve all children with the given element name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 * @param      bool   Whether or not to apply automatic singular/plural
	 *                    handling that skips plural container elements.
	 *
	 * @return     DOMNodeList A list of the child elements.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getChildren($name, $namespaceUri = null, $pluralMagic = false)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// init our vars
		$query = '';
		$singularName = null;
		
		// tag our element, because libxml will mess things up otherwise
		$marker = uniqid('', true);
		$this->setAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker', $marker);
		
		if($pluralMagic) {
			// we always assume that we either get plural names, or the singular of the singular is not different from the singular :)
			$singularName = $this->singularize($name);
			if($namespaceUri) {
				$query = 'child::*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"] | child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"]/*[local-name() = "%2$s" and namespace-uri() = "%3$s" and ../../@agavi_annotations_latest:marker = "%4$s"]';
			} else {
				$query = '%1$s[../@agavi_annotations_latest:marker = "%4$s"]/%2$s[../../@agavi_annotations_latest:marker = "%4$s"] | %2$s[../@agavi_annotations_latest:marker = "%4$s"]';
			}
		} else {
			if($namespaceUri) {
				$query = 'child::*[local-name() = "%1$s" and namespace-uri() = "%3$s" and ../@agavi_annotations_latest:marker = "%4$s"]';
			} else {
				$query = '%1$s[../@agavi_annotations_latest:marker = "%4$s"]';
			}
		}
		
		$retval = $this->ownerDocument->getXpath()->query(sprintf($query, $name, $singularName, $namespaceUri, $marker), $this);
		
		$this->removeAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker');
		
		return $retval;
	}
	
	/**
	 * Determine whether this element has a particular child element. This method
	 * succeeds only when there is exactly one child element with the given name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     bool True if there is exactly one instance of an element with
	 *                  the given name; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasChild($name, $namespaceUri = null)
	{
		return $this->getChild($name, $namespaceUri) !== null;
	}
	
	/**
	 * Return a single child element with a given name.
	 * Only returns anything if there is exactly one child of this name.
	 *
	 * @param      string The name of the element.
	 * @param      string The namespace URI. If null, the document default
	 *                    namespace will be used. If an empty string, no namespace
	 *                    will be used.
	 *
	 * @return     DOMElement The child element, or null if none exists.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getChild($name, $namespaceUri = null)
	{
		// if arg is null, then only check for elements from our default namespace
		// if namespace uri is null, use default ns. if empty string, use no ns
		$namespaceUri = ($namespaceUri === null ? $this->ownerDocument->getDefaultNamespaceUri() : $namespaceUri);
		
		// tag our element, because libxml will mess things up otherwise
		$marker = uniqid('', true);
		$this->setAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker', $marker);
		
		if($namespaceUri) {
			$query = 'self::node()[count(child::*[local-name() = "%1$s" and namespace-uri() = "%2$s" and ../@agavi_annotations_latest:marker = "%3$s"]) = 1]/*[local-name() = "%1$s" and namespace-uri() = "%2$s" and ../@agavi_annotations_latest:marker = "%3$s"]';
		} else {
			$query = 'self::node()[count(child::%1$s[../@agavi_annotations_latest:marker = "%3$s"]) = 1]/%1$s[../@agavi_annotations_latest:marker = "%3$s"]';
		}
		
		$retval = $this->ownerDocument->getXpath()->query(sprintf($query, $name, $namespaceUri, $marker), $this)->item(0);
		
		$this->removeAttributeNS(AgaviXmlConfigParser::NAMESPACE_AGAVI_ANNOTATIONS_1_0, 'agavi_annotations_latest:marker');
		
		return $retval;
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getAttribute($name, $default = null)
	{
		$retval = parent::getAttribute($name);
		
		// getAttribute returns '' when the attribute doesn't exist, but any
		// null-ish value is probably unacceptable anyway
		if($retval == null) {
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
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
	
	/**
	 * Retrieve all attributes of the element that are in no namespace.
	 *
	 * @return     array An associative array of attribute names and values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getAttributes()
	{
		return $this->getAttributesNS('');
	}
	
	/**
	 * Retrieve all attributes of the element that are in the given namespace.
	 *
	 * @return     array An associative array of attribute names and values.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getAttributesNS($namespaceUri)
	{
		$retval = array();
		
		foreach($this->ownerDocument->getXpath()->query(sprintf('@*[namespace-uri() = "%s"]', $namespaceUri), $this) as $attribute) {
			$retval[$attribute->localName] = $attribute->nodeValue;
		}
		
		return $retval;
	}
	
	/**
	 * Check whether or not the element has Agavi parameters as children.
	 *
	 * @return     bool True, if there are parameters, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasAgaviParameters()
	{
		if($this->ownerDocument->isAgaviConfiguration()) {
			return $this->has('parameters', AgaviXmlConfigParser::NAMESPACE_AGAVI_ENVELOPE_LATEST);
		}
		
		return false;
	}
	
	/**
	 * Retrieve all of the Agavi parameter elements associated with this
	 * element.
	 *
	 * @param      array An array of existing parameters.
	 * @param      bool  Whether or not input values should be literalized once
	 *                   they are read.
	 *
	 * @return     array The complete array of parameters.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getAgaviParameters(array $existing = array(), $literalize = true)
	{
		$result = $existing;
		$offset = 0;
		
		if($this->ownerDocument->isAgaviConfiguration()) {
			$elements = $this->get('parameters', AgaviXmlConfigParser::NAMESPACE_AGAVI_ENVELOPE_LATEST);
			
			foreach($elements as $element) {
				$key = null;
				if(!$element->hasAttribute('name')) {
					$result[$key = $offset++] = null;
				} else {
					$key = $element->getAttribute('name');
				}
				
				if($element->hasAgaviParameters()) {
					$result[$key] = isset($result[$key]) && is_array($result[$key]) ? $result[$key] : array();
					$result[$key] = $element->getAgaviParameters($result[$key], $literalize);
				} else {
					$result[$key] = $literalize ? AgaviToolkit::literalize($element->getValue()) : $element->getValue();
				}
			}
		}
		
		return $result;
	}
}

?>
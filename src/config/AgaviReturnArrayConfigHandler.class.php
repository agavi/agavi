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
 * AgaviReturnArrayConfigHandler allows you to retrieve the contents of a config
 * file as an array.
 * Assumes that the content elements are in no XML namespace; if you want to use
 * an XML namespace for your elements, define the namespace URI using the
 * "namespace_uri" parameter.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviReturnArrayConfigHandler extends AgaviXmlConfigHandler
{
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		$document->setDefaultNamespace($this->getParameter('namespace_uri', ''));
		
		$data = array();
		foreach($document->getConfigurationElements() as $cfg) {
			$data = array_merge($data, $this->convertToArray($cfg, true));
		}
		
		// compile data
		$code = 'return ' . var_export($data, true) . ';';
		
		return $this->generate($code, $document->documentURI);
	}

	/**
	 * Converts an AgaviXmlConfigDomElement into an array.
	 *
	 * @param      AgaviXmlConfigDomElement The configuration element to convert.
	 * @param      bool                     Whether this is a top level element.
	 *
	 * @return     array The configuration values as an array.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function convertToArray(AgaviXmlConfigDomElement $item, $topLevel = false)
	{
		$idAttribute = $this->getParameter('id_attribute', 'name');
		$valueKey = $this->getParameter('value_key', 'value');
		$forceArrayValues = $this->getParameter('force_array_values', false);
		$attributePrefix = $this->getParameter('attribute_prefix', '');
		$literalize = $this->getParameter('literalize', true);
		
		$singularParentName = AgaviInflector::singularize($item->getName());

		$data = array();

		$attribs = $item->getAttributes();
		$numAttribs = count($attribs);
		if($idAttribute && $item->hasAttribute($idAttribute)) {
			$numAttribs--;
		}
		
		foreach($item->getAttributes() as $name => $value) {
			if(($topLevel && in_array($name, array('context', 'environment'))) || $name == $idAttribute) {
				continue;
			}

			if($literalize) {
				$value = AgaviToolkit::literalize($value);
			}

			if(!isset($data[$name])) {
				$data[$attributePrefix . $name] = $value;
			}
		}
		
		if(!(int)$item->ownerDocument->getXpath()->evaluate(sprintf('count(*[namespace-uri() = "%s"])', $item->ownerDocument->getDefaultNamespaceUri()), $item)) {
			$val = $item->getValue();
			if($literalize) {
				$val = AgaviToolkit::literalize($val);
			}
			
			if($val === null) {
				$val = '';
			}
			
			if(!$topLevel && ($numAttribs || $forceArrayValues)) {
				$data[$valueKey] = $val;
			} elseif(!$topLevel) {
				$data = $val;
			}
			
		} else {
			$names = array();
			$children = $item->ownerDocument->getXpath()->query(sprintf('*[namespace-uri() = "%s"]', $item->ownerDocument->getDefaultNamespaceUri()), $item);
			foreach($children as $child) {
				$names[] = $child->getName();
			}
			$dupes = array();
			foreach(array_unique(array_diff_assoc($names, array_unique($names))) as $name) {
				$dupes[] = $name;
			}
			foreach($children as $key => $child) {
				$hasId = ($idAttribute && $child->hasAttribute($idAttribute));
				$isDupe = in_array($child->getName(), $dupes);
				$hasParent = $child->getName() == $singularParentName && $item->getName() != $singularParentName;
				if(($hasId || $isDupe) && !$hasParent) {
					// it's one of multiple tags in this level without the respective plural form as the parent node
					if(!isset($data[$idx = AgaviInflector::pluralize($child->getName())])) {
						$data[$idx] = array();
					}
					$hasParent = true;
					$to =& $data[$idx];
				} else {
					$to =& $data;
				}
				
				if($hasId) {
					$key = $child->getAttribute($idAttribute);
					if($literalize) {
						// no literalize, just constants!
						$key = AgaviToolkit::expandDirectives($key);
					}
					$to[$key] = $this->convertToArray($child);
				} elseif($hasParent) {
					$to[] = $this->convertToArray($child);
				} else {
					$to[$child->getName()] = $this->convertToArray($child);
				}
			}
		}
		
		return $data;
	}
}
?>
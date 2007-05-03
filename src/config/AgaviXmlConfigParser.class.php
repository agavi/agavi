<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviXmlConfigHandler allows you to retrieve the contents of a xml config
 * file as structured object tree
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */

class AgaviXmlConfigParser extends AgaviConfigParser
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/1.0/config';
	
	/**
	 * @var        DomXPath A DomXPath instance used to parse this document.
	 */
	protected $xpath = null;
	
	/**
	 * @var        string The encoding of the file that's being parsed here.
	 */
	protected $encoding = 'utf-8';
	
	/**
	 * @var        string The name of the config file we're parsing.
	 */
	protected $config = '';

	/**
	 * @see        AgaviConfigParser::parse()
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($config, $validationFile = null)
	{
		if(!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is unreadable';
			throw new AgaviUnreadableException($error);
		}
		
		$this->config = $config;

		// suppress errors from dom, ppl should use a proper xml editor to validate their files atm ...
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		$doc = new DOMDocument();
		$doc->load($config);
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new AgaviParseException(
				sprintf(
					'Configuration file "%s" could not be parsed due to the following error%s: ' . "\n\n%s", 
					$config, 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		$this->encoding = strtolower($doc->encoding);
		
		// replace %lala% directives in XInclude href attributes
		foreach($doc->getElementsByTagNameNS('http://www.w3.org/2001/XInclude', '*') as $element) {
			if($element->hasAttribute('href')) {
				$attribute = $element->getAttributeNode('href');
				$parts = explode('#', $attribute->nodeValue, 2);
				$parts[0] = str_replace('\\', '/', AgaviConfigHandler::replaceConstants($parts[0]));
				$attribute->nodeValue = implode('#', $parts);
			}
		}
		
		$doc->xinclude();
		if(libxml_get_last_error() !== false) {
			$throw = false;
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				if($error->level != LIBXML_ERR_WARNING) {
					$throw = true;
				}
				$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
			}
			libxml_clear_errors();
			if($throw) {
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Configuration file "%s" could not be parsed due to the following error%s that occured while resolving XInclude directives: ' . "\n\n%s", 
						$config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		// if there is no xmlns declaration on the root element, we gotta add it. must do after xinclude() to maintain BC
		if($doc->documentElement && !$doc->documentElement->namespaceURI) {
			$doc->documentElement->setAttribute('xmlns', self::XML_NAMESPACE);
			
			$reload = $doc->saveXML();
			
			$doc = new DOMDocument();
			$doc->loadXML($reload);
		}
		
		$this->xpath = new DOMXPath($doc);
		
		// remove all xml:base attributes inserted by XIncludes
		// $nodes = $this->xpath->query('//@xml:base', $doc);
		// foreach($nodes as $node) {
		// 	$node->ownerElement->removeAttributeNode($node);
		// }
		
		$stylesheetProcessingInstructions = $this->xpath->query("//processing-instruction('xml-stylesheet')", $doc);
		foreach($stylesheetProcessingInstructions as $pi) {
			$fragment = $doc->createDocumentFragment();
			$fragment->appendXml('<foo ' . $pi->data . ' />');
			$type = $fragment->firstChild->getAttribute('type');
			if(in_array($type, array('text/xml', 'text/xsl', 'application/xml', 'application/xsl+xml'))) {
				$href = $href = $fragment->firstChild->getAttribute('href');
				
				if(strpos($href, '#') === 0) {
					// embedded XSL
					$stylesheets = $this->xpath->query("//*[@id='" . substr($href, 1) . "']", $doc);
					if($stylesheets->length) {
						$xsl = new DomDocument();
						$xsl->appendChild($xsl->importNode($stylesheets->item(0), true));
						if(libxml_get_last_error() !== false) {
							$errors = array();
							foreach(libxml_get_errors() as $error) {
								$errors[] = $error->message;
							}
							libxml_clear_errors();
							libxml_use_internal_errors($luie);
							throw new AgaviParseException(
								sprintf(
									'Configuration file "%s" could not be parsed due to the following error%s that occured while loading the specified XSL stylesheet "%s": ' . "\n\n%s", 
									$config, 
									count($errors) > 1 ? 's' : '', 
									$href,
									implode("\n", $errors)
								)
							);
						}
					} else {
						throw new AgaviParseException(
							sprintf(
								'Configuration file "%s" could not be parsed because the inline stylesheet "%s" referenced in the "xml-stylesheet" processing instruction could not be found in the document.', 
								$config, 
								$href
							)
						);
					}
				} else {
					// references an xsl file
					$xsl = new DomDocument();
					$xsl->load(AgaviConfigHandler::replaceConstants($href));
					if(libxml_get_last_error() !== false) {
						$errors = array();
						foreach(libxml_get_errors() as $error) {
							$errors[] = $error->message;
						}
						libxml_clear_errors();
						libxml_use_internal_errors($luie);
						throw new AgaviParseException(
							sprintf(
								'Configuration file "%s" could not be parsed due to the following error%s that occured while loading the specified XSL stylesheet "%s": ' . "\n\n%s", 
								$config, 
								count($errors) > 1 ? 's' : '', 
								$href,
								implode("\n", $errors)
							)
						);
					}
				}

				$proc = new XSLTProcessor();
				$proc->importStylesheet($xsl);
				// libxml_get_last_error() returns false if importStylesheet failed, libxml_get_errors() works nontheless. zomfg libxml.
				// also, if we catch the errors here and throw an exception, we don't need an @ further down at transformToDoc().
				if(libxml_get_last_error() !== false || count(libxml_get_errors())) {
					$errors = array();
					foreach(libxml_get_errors() as $error) {
						$errors[] = $error->message;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($luie);
					throw new AgaviParseException(
						sprintf(
							'Configuration file "%s" could not be parsed due to the following error%s that occured while importing the specified XSL stylesheet "%s": ' . "\n\n%s", 
							$config, 
							count($errors) > 1 ? 's' : '', 
							$href,
							implode("\n", $errors)
						)
					);
				}

				$this->xpath = null;
				
				$newdoc = $proc->transformToDoc($doc);
				
				if(libxml_get_last_error() !== false) {
					$errors = array();
					foreach(libxml_get_errors() as $error) {
						$errors[] = $error->message;
					}
					libxml_clear_errors();
					libxml_use_internal_errors($luie);
					throw new AgaviParseException(
						sprintf(
							'Configuration file "%s" could not be parsed due to the following error%s that occured while transforming the document using the XSL stylesheet "%s": ' . "\n\n%s", 
							$config, 
							count($errors) > 1 ? 's' : '', 
							$href,
							implode("\n", $errors)
						)
					);
				}

				if($newdoc) {
					$doc = $newdoc;
				}
				
				$this->xpath = new DOMXPath($doc);
				
				$pi->parentNode->removeChild($pi);
				
				break;
			}
		}
		
		if($doc->documentElement) {
			$this->xpath->registerNamespace('agavi', $doc->documentElement->namespaceURI);
		
			// remove top-level <sandbox> elements
			$sandboxes = $this->xpath->query('/agavi:configurations/agavi:sandbox', $doc);
			foreach($sandboxes as $sandbox) {
				$sandbox->parentNode->removeChild($sandbox);
			}
		}
		
		if($validationFile) {
			if(!is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $config . '" does not exist or is unreadable';
				throw new AgaviUnreadableException($error);
			}
			if(!$doc->schemaValidate($validationFile)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
		
		$rootRes = new AgaviConfigValueHolder();
		
		if($doc->documentElement) {
			$this->parseNodes(array($doc->documentElement), $rootRes);
		}
		
		return $rootRes;
	}

	/**
	 * Iterates thru a list of nodes and stores to each node in the 
	 * <b>XmlValueHolder</b>
	 *
	 * @param      mixed An array or an object that can be iterated over
	 * @param      AgaviXmlValueHolder The storage for the info from the nodes
	 * @param      bool Whether this list is the singular form of the parent node
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseNodes($nodes, AgaviConfigValueHolder $parentVh, $isSingular = false)
	{
		foreach($nodes as $node) {
			if($node->nodeType == XML_ELEMENT_NODE && (!$node->namespaceURI || $node->namespaceURI == self::XML_NAMESPACE)) {
				$vh = new AgaviConfigValueHolder();
				$nodeName = $this->convertEncoding($node->localName);
				$vh->setName($nodeName);
				$parentVh->addChildren($nodeName, $vh);

				foreach($node->attributes as $attribute) {
					if((!$attribute->namespaceURI || $attribute->namespaceURI == self::XML_NAMESPACE)) {
						$vh->setAttribute($this->convertEncoding($attribute->localName), $this->convertEncoding($attribute->nodeValue));
					}
				}

				// there are no child nodes so we set the node text contents as the value for the valueholder
				if($this->xpath->query('*', $node)->length == 0) {
					$vh->setValue($this->convertEncoding($node->nodeValue));
				}

				if($node->hasChildNodes()) {
					$this->parseNodes($node->childNodes, $vh);
				}
			}
		}
	}
	
	/**
	 * Handle encoding for a value, i.e. translate from UTF-8 if necessary.
	 *
	 * @param      string A UTF-8 string value from the DomDocument.
	 *
	 * @return     string A value in the correct encoding of the parsed document.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function convertEncoding($value)
	{
		if($this->encoding == 'utf-8') {
			return $value;
		} elseif($this->encoding == 'iso-8859-1') {
			return utf8_decode($value);
		} elseif(function_exists('iconv')) {
			return iconv('UTF-8', $this->encoding, $value);
		} else {
			throw new AgaviParseException('No iconv module available, configuration file "' . $this->config . '" with input encoding "' . $this->encoding . '" cannot be parsed.');
		}
	}
}
?>
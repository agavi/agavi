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
				$element->setAttribute('href', $lala = AgaviConfigHandler::replaceConstants($element->getAttribute('href')));
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
		
		$this->xpath = new DomXPath($doc);
		
		// remove all xml:base attributes inserted by XIncludes
		$nodes = $this->xpath->query('//@xml:base', $doc);
		foreach($nodes as $node) {
			$node->ownerElement->removeAttributeNode($node);
		}
		
		// remove top-level <sandbox> elements
		$sandboxes = $this->xpath->query('/configurations/sandbox', $doc);
		foreach($sandboxes as $sandbox) {
			$sandbox->parentNode->removeChild($sandbox);
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

		$this->parseNodes(array($doc->documentElement), $rootRes);

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
			if($node->nodeType == XML_ELEMENT_NODE) {
				$vh = new AgaviConfigValueHolder();
				$vh->setName($this->convertEncoding($node->nodeName));
				$parentVh->addChildren($this->convertEncoding($node->tagName), $vh);

				foreach($node->attributes as $attribute) {
					$vh->setAttribute($this->convertEncoding($attribute->name), $this->convertEncoding($attribute->value));
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
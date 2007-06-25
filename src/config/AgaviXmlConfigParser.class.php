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
class AgaviXmlConfigParser
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/1.0/config';
	
	const VALIDATION_TYPE_XMLSCHEMA = 'xml_schema';
	
	const VALIDATION_TYPE_RELAXNG = 'relax_ng';
	
	const VALIDATION_TYPE_SCHEMATRON = 'schematron';
	
	/**
	 * @var        string The path to the config file we're currently parsing.
	 */
	protected $config = '';
	
	/**
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      array  An associative array of validation information.
	 *
	 * @return     array An array of DOMDocuments (from child to parent).
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parseAll($config, array $validation = array())
	{
		$retval = array();
		
		$nextConfig = $config;
		
		while($nextConfig !== null) {
			$doc = $this->parse($nextConfig, $validation);
			
			if($doc->documentElement && $doc->documentElement->hasAttribute('parent')) {
				$nextConfig = AgaviToolkit::literalize($doc->documentElement->getAttribute('parent'));
			} else {
				$nextConfig = null;
			}
			
			$retval[] = $doc;
		}
		
		return $retval;
	}
	
	/**
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      array  An associative array of validation information.
	 *
	 * @return     DOMDocument A DOMDocument.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($config, array $validation = array())
	{
		if(!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is unreadable';
			throw new AgaviUnreadableException($error);
		}
		
		$doc = $this->load($config);
		
		$this->transform($doc);
		
		$this->validate($doc, $validation);
		
		$this->cleanup($doc);
		
		return $doc;
	}
	
	/**
	 * Load the configuration file into DOM and resolve XIncludes.
	 *
	 * @param      string The path to the configuration file.
	 *
	 * @return     DOMDocument The loaded document.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function load($config)
	{
		$this->config = $config;
		
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
		
		// replace %lala% directives in XInclude href attributes
		foreach($doc->getElementsByTagNameNS('http://www.w3.org/2001/XInclude', '*') as $element) {
			if($element->hasAttribute('href')) {
				$attribute = $element->getAttributeNode('href');
				$parts = explode('#', $attribute->nodeValue, 2);
				$parts[0] = str_replace('\\', '/', AgaviToolkit::expandDirectives($parts[0]));
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
		
		$xpath = new DOMXPath($doc);
		
		// remove all xml:base attributes inserted by XIncludes
		$nodes = $xpath->query('//@xml:base', $doc);
		foreach($nodes as $node) {
			$node->ownerElement->removeAttributeNode($node);
		}
		
		// if there is no xmlns declaration on the root element, we gotta add it. must do after xinclude() to maintain BC
		if($doc->documentElement && !$doc->documentElement->namespaceURI) {
			$doc->documentElement->setAttribute('xmlns', self::XML_NAMESPACE);
			
			$reload = $doc->saveXML();
			
			$doc = new DOMDocument();
			$doc->loadXML($reload);
		}
		
		libxml_use_internal_errors($luie);
		
		return $doc;
	}
	
	/**
	 * Transform the document using info from embedded processing instructions.
	 *
	 * @param      DOMDocument The document to transform.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function transform(DOMDocument &$doc)
	{
		$luie = libxml_use_internal_errors(true);
		
		$xpath = new DOMXPath($doc);
		
		$stylesheetProcessingInstructions = $xpath->query("//processing-instruction('xml-stylesheet')", $doc);
		foreach($stylesheetProcessingInstructions as $pi) {
			$fragment = $doc->createDocumentFragment();
			$fragment->appendXml('<foo ' . $pi->data . ' />');
			$type = $fragment->firstChild->getAttribute('type');
			if(in_array($type, array('text/xml', 'text/xsl', 'application/xml', 'application/xsl+xml'))) {
				$href = $href = $fragment->firstChild->getAttribute('href');
				
				if(strpos($href, '#') === 0) {
					// embedded XSL
					$stylesheets = $xpath->query("//*[@id='" . substr($href, 1) . "']", $doc);
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
									$this->config, 
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
								$this->config, 
								$href
							)
						);
					}
				} else {
					// references an xsl file
					$xsl = new DomDocument();
					$xsl->load(AgaviToolkit::expandDirectives($href));
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
								$this->config, 
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
							$this->config, 
							count($errors) > 1 ? 's' : '', 
							$href,
							implode("\n", $errors)
						)
					);
				}
				
				unset($xpath);
				
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
							$this->config, 
							count($errors) > 1 ? 's' : '', 
							$href,
							implode("\n", $errors)
						)
					);
				}
				
				if($newdoc) {
					$doc = $newdoc;
				}
				
				$pi->parentNode->removeChild($pi);
				
				break;
			}
		}
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Load the file into DOM, resolve XIncludes, apply XSL, validate against XSD.
	 *
	 * @param      string The path to the XML file
	 * @param      string The path to the validation file.
	 *
	 * @return     DOMDocument The fully loaded and transformed DOM document.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validate(DOMDocument $doc, array $validationInfo = array())
	{
		foreach($validationInfo as $type => $files) {
			switch($type) {
				case self::VALIDATION_TYPE_XMLSCHEMA:
					$this->validateXmlschema($doc, (array) $files);
					break;
				case self::VALIDATION_TYPE_RELAXNG:
					$this->validateRelaxng($doc, (array) $files);
					break;
				case self::VALIDATION_TYPE_SCHEMATRON:
					$this->validateSchematron($doc, (array) $files);
					break;
			}
		}
		
		$sources = array();
		
		if($doc->documentElement->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation')) {
			$locations = preg_split('/\s+/', $doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation'));
			for($i = 1; $i < count($locations); $i = $i + 2) {
				$sources[] = $locations[$i];
			}
		}
		if($doc->documentElement->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation')) {
			$sources[] = $doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation');
		}
		
		if($sources) {
			foreach($sources as &$source) {
				$source = AgaviToolkit::expandDirectives($source);
				$info = parse_url($source);
				if(!isset($info['scheme']) && !AgaviToolkit::isPathAbsolute($source)) {
					// the schema location is relative to the XML file
					$source = dirname($this->config) . DIRECTORY_SEPARATOR . $source;
				}
				$source = file_get_contents($source);
			}
			$this->validateXmlschema($doc, array(), $sources);
		}
	}
	
	/**
	 * Clean up the document.
	 *
	 * @param      DOMDocument The document to clean up.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function cleanup(DOMDocument $doc)
	{
		$xpath = new DOMXPath($doc);
		
		if($doc->documentElement && $doc->documentElement->namespaceURI == self::XML_NAMESPACE) {
			$xpath->registerNamespace('agavi', $doc->documentElement->namespaceURI);
			// remove top-level <sandbox> elements
			$sandboxes = $xpath->query('/agavi:configurations/agavi:sandbox', $doc);
			foreach($sandboxes as $sandbox) {
				$sandbox->parentNode->removeChild($sandbox);
			}
		}
		
		unset($xpath);
	}
	
	/**
	 * Validate the document against the given list of XML Schema files.
	 *
	 * @param      DOMDocument The document to validate.
	 * @param      array       An array of file names to validate.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateXmlschema(DOMDocument $doc, array $validationFiles = array(), array $validationSources = array())
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationFiles as $validationFile) {
			if(!is_resource($validationFile) && !is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->config . '" does not exist or is unreadable';
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
						$this->config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		foreach($validationSources as $validationSource) {
			if(!$doc->schemaValidateSource($validationSource)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$this->config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Validate the document against the given list of RELAX NG files.
	 *
	 * @param      DOMDocument The document to validate.
	 * @param      array       An array of file names to validate.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateRelaxng(DOMDocument $doc, array $validationFiles = array(), array $validationSources = array())
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationFiles as $validationFile) {
			if(!is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->config . '" does not exist or is unreadable';
				throw new AgaviUnreadableException($error);
			}
			
			if(!$doc->relaxNGValidate($validationFile)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$this->config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		foreach($validationSources as $validationSource) {
			if(!$doc->relaxNGValidateSource($validationSource)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$this->config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Validate the document against the given list of Schematron files.
	 *
	 * @param      DOMDocument The document to validate.
	 * @param      array       An array of file names to validate.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateSchematron(DOMDocument $doc, array $validationFiles = array())
	{
		// not yet implemented
		return;
		
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationFiles as $validationFile) {
			if(!is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->config . '" does not exist or is unreadable';
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
						$this->config, 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
	}
}

?>
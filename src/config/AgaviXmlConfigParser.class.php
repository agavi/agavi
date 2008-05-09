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
 * AgaviXmlConfigParser handles both Agavi and foreign XML configuration files,
 * deals with XIncludes, XSL transformations and validation as well as filtering
 * and ordering of configuration blocks and parent file resolution and parsing.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviXmlConfigParser
{
	const AGAVI_ENVELOPE_NAMESPACE_1_0 = 'http://agavi.org/agavi/1.0/config';
	
	const AGAVI_ENVELOPE_NAMESPACE_LATEST = self::AGAVI_ENVELOPE_NAMESPACE_1_0;
	
	const VALIDATION_TYPE_XMLSCHEMA = 'xml_schema';
	
	const VALIDATION_TYPE_RELAXNG = 'relax_ng';
	
	const VALIDATION_TYPE_SCHEMATRON = 'schematron';
	
	/**
	 * @var        array A list of XML namespaces for Agavi configuration files.
	 */
	public static $agaviEnvelopeNamespaces = array(
		self::AGAVI_ENVELOPE_NAMESPACE_1_0,
	);
	
	/**
	 * @var        string The path to the config file we're currently parsing.
	 */
	protected $path = '';
	
	public static function isAgaviEnvelopeNamespace($namespaceUri)
	{
		return in_array($namespaceUri, self::$agaviEnvelopeNamespaces);
	}
	
	/**
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      array  An associative array of validation information.
	 * @param      string The environment name.
	 * @param      string The context name.
	 *
	 * @return     DOMDocument A properly merged DOMDocument.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function execute($path, array $validation = array(), $environment, $context = null)
	{
		$isAgaviConfigFormat = true;
		// build an array of documents (this one, and the parents)
		$docs = array();
		$nextPath = $path;
		while($nextPath !== null) {
			$parser = new AgaviXmlConfigParser();
			$doc = $parser->parse($nextPath, $validation);
			$doc->xpath = new DOMXPath($doc);
			$docs[] = $doc;
			
			// make sure it (still) is a <configurations> file with the proper agavi namespace
			if($isAgaviConfigFormat) {
				$isAgaviConfigFormat = $doc->documentElement && $doc->documentElement->nodeName == 'configurations' && self::isAgaviEnvelopeNamespace($doc->documentElement->namespaceURI);
			}
			
			// is it an agavi <configurations> element? does it have a parent attribute? yes? good. parse that next
			// TODO: support future namespaces
			if($isAgaviConfigFormat && $doc->documentElement->hasAttribute('parent')) {
				$nextPath = AgaviToolkit::literalize($doc->documentElement->getAttribute('parent'));
			} else {
				$nextPath = null;
			}
		}
		
		// TODO: use our own classes here that extend DOM*
		$retval = new AgaviXmlConfigDomDocument();
		
		if($isAgaviConfigFormat) {
			
			$retval->appendChild(new AgaviXmlConfigDomElement('configurations', null, self::AGAVI_LATEST_CONFIG_XML_NAMESPACE));
		
			// reverse the array - we want the parents first!
			$docs = array_reverse($docs);
		
			$configurationElements = array();
		
			// TODO: I bet this leaks memory due to the nodes being taken out of the docs. beware circular refs!
			foreach($docs as $doc) {
				// iterate over all nodes (attributes, <sandbox>, <configuration> etc) inside the document element and append them to the <configurations> element in our final document
				foreach($doc->documentElement->childNodes as $node) {
					if($node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'configuration' && self::isAgaviEnvelopeNamespace($node->namespaceURI)) {
						// it's a <configuration> element - put that on a stack for processing
						$configurationElements[] = $node;
					} else {
						// import the node, recursively, and store the imported node
						$importedNode = $retval->importNode($node, true);
						// now append it to the <configurations> element
						$retval->documentElement->appendChild($importedNode);
					}
				}
			}
		
			$configurationOrder = array(
				'count(self::node()[not(@environment) and not(@context)])',
				'count(self::node()[@environment and not(@context)])',
				'count(self::node()[not(@environment) and @context])',
				'count(self::node()[@environment and @context])',
			);
			$testAttributes = array(
				'context' => $context,
				'environment' => $environment,
			);
		
			// we sort the nodes - generic ones first, then those that are per-environment, then those per-context, then those per-both
			foreach($configurationOrder as $xpath) {
				foreach($configurationElements as &$element) {
					if($element->ownerDocument->xpath->evaluate($xpath, $element)) {
						foreach($testAttributes as $attributeName => $attributeValue) {
							// TODO: move that method or something
							if($element->hasAttribute($attributeName) && !AgaviConfigHandler::testPattern($element->getAttribute($attributeName), $attributeValue)) {
								continue 2;
							}
						}
						$importedNode = $retval->importNode($element, true);
						$retval->documentElement->appendChild($importedNode);
					}
				}
			}
		} else {
			// it's not an agavi config file. just pass it through then
			$retval->appendChild($retval->importNode($doc->documentElement, true));
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
	public function parse($path, array $validation = array())
	{
		if(!is_readable($path)) {
			$error = 'Configuration file "' . $path . '" does not exist or is unreadable';
			throw new AgaviUnreadableException($error);
		}
		
		$doc = $this->load($path);
		
		$this->transform($doc);
		
		$this->validate($doc, $validation);
		
		$this->cleanup($doc);
		
		return $doc;
	}
	
	/**
	 * Load the configuration file and resolve XIncludes.
	 *
	 * @param      string The path to the configuration file.
	 *
	 * @return     DOMDocument A document with.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function load($path)
	{
		$this->path = $path;
		
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		$doc = new DOMDocument();
		$doc->load($path);
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
					$path, 
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
						$path, 
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
		
		$needsReload = false;
		
		// TODO: get rid of this for 1.0
		// if there is no xmlns declaration on the root element, we gotta add it. must do after xinclude() to maintain BC
		if($doc->documentElement && !$doc->documentElement->namespaceURI) {
			$doc->documentElement->setAttribute('xmlns', self::AGAVI_LATEST_CONFIG_XML_NAMESPACE);
			
			$needsReload = true;
		}
		
		// necessary due to a PHP bug, see http://trac.agavi.org/ticket/621 and http://bugs.php.net/bug.php?id=43364
		if(version_compare(PHP_VERSION, '5.2.6', '<')) {
			$needsReload = true;
		}
		
		// reload the doc maybe?
		if($needsReload) {
			// we need to remember the document URI and restore it, just in case
			$documentUri = $doc->documentURI;
			$doc->loadXML($doc->saveXML());
			$doc->documentURI = $documentUri;
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
									$this->path, 
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
								$this->path, 
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
								$this->path, 
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
							$this->path, 
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
							$this->path, 
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
		if(AgaviConfig::get('core.skip_config_validation', false)) {
			return;
		}
		
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
					$source = dirname($this->path) . DIRECTORY_SEPARATOR . $source;
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
		
		if($doc->documentElement && self::isAgaviEnvelopeNamespace($doc->documentElement->namespaceURI)) {
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
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->path . '" does not exist or is unreadable';
				throw new AgaviUnreadableException($error);
			}
			
			// gotta do the @ to suppress warnings when the schema cannot be found
			if(!@$doc->schemaValidate($validationFile)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$this->path, 
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
						$this->path, 
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
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->path . '" does not exist or is unreadable';
				throw new AgaviUnreadableException($error);
			}
			
			// gotta do the @ to suppress warnings when the schema cannot be found
			if(!@$doc->relaxNGValidate($validationFile)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'XML Schema validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
						$this->path, 
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
						$this->path, 
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
				$error = 'Validation file "' . $validationFile . '" for configuration file "' . $this->path . '" does not exist or is unreadable';
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
						$this->path, 
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
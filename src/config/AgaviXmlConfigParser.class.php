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
	
	const SCHEMATRON_ISO_NAMESPACE = 'http://purl.oclc.org/dsdl/schematron';
	
	const SVRL_ISO_NAMESPACE = 'http://purl.oclc.org/dsdl/svrl';
	
	const XSL_NAMESPACE_1999 = 'http://www.w3.org/1999/XSL/Transform';
	
	/**
	 * @var        array A list of XML namespaces for Agavi configuration files as
	 *                   keys and their associated XPath namespace prefix (value).
	 */
	public static $agaviEnvelopeNamespaces = array(
		self::AGAVI_ENVELOPE_NAMESPACE_1_0 => 'agavi_envelope_1_0',
	);
	
	/**
	 * @var        string Path to the config file we're parsing in this instance.
	 */
	protected $path = '';
	
	/**
	 * @var        string The name of the current environment.
	 */
	protected $environment = '';
	
	/**
	 * @var        string The name of the current context.
	 */
	protected $context = null;
	
	/**
	 * @var        DOMDocument The document we're parsing here.
	 */
	protected $doc = null;
	
	/**
	 * @var        DOMXPath The XPath instance for the current document.
	 */
	protected $xpath = null;
	
	/**
	 * Test if the given document looks like an Agavi config file.
	 *
	 * @param      DOMDocument The document to test.
	 *
	 * @return     bool True, if it is an Agavi config document, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public static function isAgaviConfigurationDocument(DOMDocument $doc)
	{
		return $doc->documentElement && $doc->documentElement->localName == 'configurations' && self::isAgaviEnvelopeNamespace($doc->documentElement->namespaceURI);
	}
	
	/**
	 * Check if the given namespace URI is a valid Agavi envelope namespace.
	 *
	 * @param      string The namespace URI.
	 *
	 * @return     bool True, if the given URI is a valid namespace URI, or false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public static function isAgaviEnvelopeNamespace($namespaceUri)
	{
		return isset(self::$agaviEnvelopeNamespaces[$namespaceUri]);
	}
	
	/**
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string The environment name.
	 * @param      string The optional context name.
	 * @param      array  An associative array of transformation information.
	 * @param      array  An associative array of validation information.
	 *
	 * @return     DOMDocument A properly merged DOMDocument.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function run($path, $environment, $context = null, array $transformationInfo = array(), array $validationInfo = array())
	{
		$isAgaviConfigFormat = true;
		// build an array of documents (this one, and the parents)
		$docs = array();
		$nextPath = $path;
		while($nextPath !== null) {
			$parser = new AgaviXmlConfigParser($nextPath, $environment, $context);
			$doc = $parser->execute($transformationInfo, $validationInfo);
			$doc->xpath = new DOMXPath($doc);
			$docs[] = $doc;
			
			// make sure it (still) is a <configurations> file with the proper agavi namespace
			if($isAgaviConfigFormat) {
				$isAgaviConfigFormat = self::isAgaviConfigurationDocument($doc);
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
		foreach(self::$agaviEnvelopeNamespaces as $envelopeNamespaceUri => $envelopeNamespacePrefix) {
			$retval->getXpath()->registerNamespace($envelopeNamespacePrefix, $envelopeNamespaceUri);
		}
		
		if($isAgaviConfigFormat) {
			
			$retval->appendChild(new AgaviXmlConfigDomElement('configurations', null, self::AGAVI_ENVELOPE_NAMESPACE_LATEST));
		
			// reverse the array - we want the parents first!
			$docs = array_reverse($docs);
		
			$configurationElements = array();
		
			// TODO: I bet this leaks memory due to the nodes being taken out of the docs. beware circular refs!
			foreach($docs as $doc) {
				// iterate over all nodes (attributes, <sandbox>, <configuration> etc) inside the document element and append them to the <configurations> element in our final document
				foreach($doc->documentElement->childNodes as $node) {
					if($node->nodeType == XML_ELEMENT_NODE && $node->localName == 'configuration' && self::isAgaviEnvelopeNamespace($node->namespaceURI)) {
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
				'count(self::node()[@agavi_envelope_1_0:matched and not(@environment) and not(@context)])',
				'count(self::node()[@agavi_envelope_1_0:matched and @environment and not(@context)])',
				'count(self::node()[@agavi_envelope_1_0:matched and not(@environment) and @context])',
				'count(self::node()[@agavi_envelope_1_0:matched and @environment and @context])',
			);
			$testAttributes = array(
				'context' => $context,
				'environment' => $environment,
			);
		
			// we sort the nodes - generic ones first, then those that are per-environment, then those per-context, then those per-both
			foreach($configurationOrder as $xpath) {
				foreach($configurationElements as &$element) {
					if($element->ownerDocument->xpath->evaluate($xpath, $element)) {
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
	 * Builds a proper regular expression from the input pattern to test against
	 * the given subject. This is for "environment" and "context" attributes of
	 * configuration blocks in the files.
	 *
	 * @param      string A regular expression chunk without delimiters/anchors.
	 *
	 * @return     bool Whether or not the subject matched the pattern.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public static function testPattern($pattern, $subject)
	{
		$pattern = preg_quote($pattern, '/');
		return (preg_match('/^(' . implode('|', array_map('trim', explode(' ', $pattern))) . ')$/', $subject) > 0);
	}
	
	/**
	 * The constructor.
	 * Will make a DOMDocument instance using the given path.
	 *
	 * @param      string The path to the configuration file.
	 * @param      string The optional name of the current environment.
	 * @param      string The optional name of the current context.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function __construct($path, $environment = null, $context = null)
	{
		if($environment === null) {
			$environment = AgaviConfig::get('core.environment');
		}
		$this->environment = $environment;
		
		$this->context = $context;
		
		if(!is_readable($path)) {
			$error = 'Configuration file "' . $path . '" does not exist or is unreadable';
			throw new AgaviUnreadableException($error);
		}
		
		$this->path = $path;
		
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$this->doc = new AgaviXmlConfigDomDocument();
		$this->doc->load($path);
		
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
		
		$this->xpath = new DOMXPath($this->doc);
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Destructor to do the cleaning up.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function __destruct()
	{
		unset($this->xpath);
		unset($this->doc);
	}
	
	/**
	 * @param      array An array of XSL paths for transformation.
	 * @param      array An associative array of validation information.
	 *
	 * @return     DOMDocument Our DOMDocument.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(array $transformationInfo = array(), array $validationInfo = array())
	{
		$this->prepare();
		
		$this->transform($transformationInfo);
		
		$this->validate($validationInfo);
		
		$this->cleanup();
		
		return $this->doc;
	}
	
	/**
	 * Prepare the configuration file: resolve XIncludes, validate against XML
	 * Schema instances declared on the document, and set processing information
	 * flags on <configuration> elements.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function prepare()
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// replace %lala% directives in XInclude href attributes
		foreach($this->doc->getElementsByTagNameNS('http://www.w3.org/2001/XInclude', '*') as $element) {
			if($element->hasAttribute('href')) {
				$attribute = $element->getAttributeNode('href');
				$parts = explode('#', $attribute->nodeValue, 2);
				$parts[0] = str_replace('\\', '/', AgaviToolkit::expandDirectives($parts[0]));
				$attribute->nodeValue = implode('#', $parts);
			}
		}
		
		$this->doc->xinclude();
		
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
		
		// remove all xml:base attributes inserted by XIncludes
		$nodes = $this->xpath->query('//@xml:base', $this->doc);
		foreach($nodes as $node) {
			$node->ownerElement->removeAttributeNode($node);
		}
		
		// necessary due to a PHP bug, see http://trac.agavi.org/ticket/621 and http://bugs.php.net/bug.php?id=43364
		if(version_compare(PHP_VERSION, '5.2.6', '<')) {
			// we need to remember the document URI and restore it, just in case
			$documentUri = $this->doc->documentURI;
			$this->doc->loadXML($this->doc->saveXML());
			$this->doc->documentURI = $documentUri;
			
			$this->xpath = new DOMXPath($this->doc);
		}
		
		libxml_use_internal_errors($luie);
		
		// next, find (and validate against) XML schema instance declarations
		$sources = array();
		if($this->doc->documentElement->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation')) {
			$locations = preg_split('/\s+/', $this->doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation'));
			for($i = 1; $i < count($locations); $i = $i + 2) {
				$sources[] = $locations[$i];
			}
		}
		if($this->doc->documentElement->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation')) {
			$sources[] = $this->doc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation');
		}
		if($sources) {
			$schemas = array();
			foreach($sources as &$source) {
				$source = AgaviToolkit::expandDirectives($source);
				if(parse_url($source, PHP_URL_SCHEME) === null && !AgaviToolkit::isPathAbsolute($source)) {
					// the schema location is relative to the XML file
					$source = dirname($this->path) . DIRECTORY_SEPARATOR . $source;
				}
				$schema = @file_get_contents($source);
				if($schema === false) {
					$error = 'XML Schema validation file "' . $source . '" for configuration file "' . $this->path . '" does not exist or is unreadable';
					throw new AgaviUnreadableException($error);
				}
				$schemas[] = $schema;
			}
			$this->validateXmlschemaSource($schemas);
		}
		
		if($this->doc->isAgaviConfiguration()) {
			$testAttributes = array(
				'context' => $this->context,
				'environment' => $this->environment,
			);
			
			foreach($this->doc->getConfigurationElements() as $configuration) {
				$matched = true;
				foreach($testAttributes as $attributeName => $attributeValue) {
					if($configuration->hasAttribute($attributeName)) {
						$matched = $matched && self::testPattern($configuration->getAttribute($attributeName), $attributeValue);
					}
				}
				if($matched) {
					$configuration->setAttributeNS(self::AGAVI_ENVELOPE_NAMESPACE_LATEST, 'matched', 'true');
				}
			}
		}
	}
	
	/**
	 * Transform the document using info from embedded processing instructions
	 * and given stylesheets.
	 *
	 * @param      array  An array of transformation information.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function transform(array $transformationInfo = array())
	{
		$transformations = array();
		
		$luie = libxml_use_internal_errors(true);
		
		$stylesheetProcessingInstructions = $this->xpath->query("//processing-instruction('xml-stylesheet')", $this->doc);
		foreach($stylesheetProcessingInstructions as $pi) {
			$fragment = $this->doc->createDocumentFragment();
			$fragment->appendXml('<foo ' . $pi->data . ' />');
			$type = $fragment->firstChild->getAttribute('type');
			if(in_array($type, array('text/xml', 'text/xsl', 'application/xml', 'application/xsl+xml'))) {
				$href = $href = $fragment->firstChild->getAttribute('href');
				
				if(strpos($href, '#') === 0) {
					// embedded XSL
					$stylesheets = $this->xpath->query("//*[@id='" . substr($href, 1) . "']", $this->doc);
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
						libxml_use_internal_errors($luie);
						throw new AgaviParseException(
							sprintf(
								'Configuration file "%s" could not be parsed because the inline stylesheet "%s" referenced in the "xml-stylesheet" processing instruction could not be found in the document.', 
								$this->path, 
								$href
							)
						);
					}
					
					$transformations[] = $xsl;
				} else {
					// references an xsl file
					$transformationInfo[] = AgaviToolkit::expandDirectives($href);
				}
				
				$pi->parentNode->removeChild($pi);
			}
		}
		
		foreach($transformationInfo as $href) {
			$xsl = new DomDocument();
			$xsl->load($href);
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
			
			$transformations[] = $xsl;
		}
		
		foreach($transformations as $xsl) {
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
			
			// set some info (config file path, context name, environment name) as params
			// first arg is the namespace URI, which PHP doesn't support. awesome. see http://bugs.php.net/bug.php?id=30622 for the sad details
			// we could use "agavi:context" etc, that does work even without such a prefix being declared in the stylesheet, but that would be completely non-XML-ish, confusing, and against the spec. so we use dots instead.
			$proc->setParameter('', array(
				'agavi.config_path' => $this->path,
				'agavi.environment' => $this->environment,
				'agavi.context' => $this->context,
			));
		
			$newdoc = $proc->transformToDoc($this->doc);
		
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
				$this->doc = $newdoc;
			}
		}
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Perform validation on this document.
	 *
	 * @param      array An array of validation information.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validate(array $validationInfo = array())
	{
		if(AgaviConfig::get('core.skip_config_validation', false)) {
			return;
		}
		
		foreach($validationInfo as $type => $files) {
			switch($type) {
				case self::VALIDATION_TYPE_XMLSCHEMA:
					$this->validateXmlschema((array) $files);
					break;
				case self::VALIDATION_TYPE_RELAXNG:
					$this->validateRelaxng((array) $files);
					break;
				case self::VALIDATION_TYPE_SCHEMATRON:
					$this->validateSchematron((array) $files);
					break;
			}
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
	public function cleanup()
	{
		if($this->doc->documentElement && self::isAgaviEnvelopeNamespace($this->doc->documentElement->namespaceURI)) {
			$this->xpath->registerNamespace('agavi', $this->doc->documentElement->namespaceURI);
			// remove top-level <sandbox> elements
			$sandboxes = $this->xpath->query('/agavi:configurations/agavi:sandbox', $this->doc);
			foreach($sandboxes as $sandbox) {
				$sandbox->parentNode->removeChild($sandbox);
			}
		}
	}
	
	/**
	 * Validate the document against the given list of XML Schema files.
	 *
	 * @param      array An array of file names to validate against.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateXmlschema(array $validationFiles = array())
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationFiles as $validationFile) {
			if(!is_resource($validationFile) && !is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				throw new AgaviUnreadableException(
					sprintf(
						'XML Schema validation file "%s" for configuration file "%s" does not exist or is unreadable',
						$validationFile,
						$this->path
					)
				);
			}
			
			// gotta do the @ to suppress warnings when the schema cannot be found
			if(!@$this->doc->schemaValidate($validationFile)) {
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
	 * Validate the document against the given list of XML Schema documents.
	 *
	 * @param      array An array of schema documents to validate against.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function validateXmlschemaSource(array $validationSources = array())
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationSources as $validationSource) {
			if(!@$this->doc->schemaValidateSource($validationSource)) {
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
	 * @param      array An array of file names to validate against.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateRelaxng(array $validationFiles = array())
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($validationFiles as $validationFile) {
			if(!is_readable($validationFile)) {
				libxml_use_internal_errors($luie);
				throw new AgaviUnreadableException(
					sprintf(
						'RELAX NG validation file "%s" for configuration file "%s" does not exist or is unreadable',
						$validationFile,
						$this->path
					)
				);
			}
			
			// gotta do the @ to suppress warnings when the schema cannot be found
			if(!@$this->doc->relaxNGValidate($validationFile)) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'RELAX NG validation of configuration file "%s" failed due to the following error%s: ' . "\n\n%s", 
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
	 * @param      array An array of file names to validate against.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validateSchematron(array $validationFiles = array())
	{
		// first, we load the schematron implementation. this is an XSL document that is used to transform a .sch file to another XSL document that is then used to transform the input document. the result is informational output about the validation, which in our case must be valid ISO SVRL, an XML schema validation reporting format
		$schematronIsoSvrlImplementation = new DOMDocument();
		$schematronIsoSvrlImplementation->load(AgaviConfig::get('core.agavi_dir') . '/config/schematron/iso_svrl.xsl');
		$schematron = new XSLTProcessor();
		$schematron->importStylesheet($schematronIsoSvrlImplementation);
		// set some info (config file path, context name, environment name) as params
		// first arg is the namespace URI, which PHP doesn't support. awesome. see http://bugs.php.net/bug.php?id=30622 for the sad details
		// we could use "agavi:context" etc, that does work even without such a prefix being declared in the stylesheet, but that would be completely non-XML-ish, confusing, and against the spec. so we use dots instead.
		$schematron->setParameter('', array(
			'agavi.config_path' => $this->path,
			'agavi.environment' => $this->environment,
			'agavi.context' => $this->context,
		));
		
		$luie = libxml_use_internal_errors(true);
		
		// loop over all validation files. those are .sch schematron schemas, which we transform to an XSL document that is then used to validate the source document :)
		foreach($validationFiles as $href) {
			if(!is_readable($href)) {
				libxml_use_internal_errors($luie);
				throw new AgaviUnreadableException(
					sprintf(
						'Schematron validation file "%s" for configuration file "%s" does not exist or is unreadable',
						$href,
						$this->path
					)
				);
			}
			
			// load the .sch file
			$sch = new DomDocument();
			$sch->load($href);
			if(libxml_get_last_error() !== false) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = $error->message;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Schematron validation of configuration file "%s" failed due to the following error%s that occured while loading schema file "%s": ' . "\n\n%s", 
						$this->path, 
						count($errors) > 1 ? 's' : '', 
						$href,
						implode("\n", $errors)
					)
				);
			}
			
			if(!$sch->documentElement || $sch->documentElement->namespaceURI != self::SCHEMATRON_ISO_NAMESPACE) {
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Schematron validation of configuration file "%s" failed because schema file "%s" is invalid', 
						$this->path, 
						$href
					)
				);
			}
			
			// transform the .sch file to a validation stylesheet using the schematron implementation
			$schema = $schematron->transformToDoc($sch);
			if($schema) {
				// it transformed fine. but did we get a proper stylesheet instance at all? wrong namespaces can lead to empty docs that only have an XML prolog
				if(!$schema->documentElement || $schema->documentElement->namespaceURI != self::XSL_NAMESPACE_1999) {
					libxml_use_internal_errors($luie);
					throw new AgaviParseException(
						sprintf(
							'Schematron validation of configuration file "%s" failed because schema file "%s" resulted in an invalid stylesheet', 
							$this->path, 
							$href
						)
					);
				}
				
				$validator = new XSLTProcessor();
				$validator->importStylesheet($schema);
			}
			
			if(libxml_get_last_error() !== false) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = $error->message;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Schematron validation of configuration file "%s" failed due to the following error%s that occured while processing the schema file "%s": ' . "\n\n%s", 
						$this->path, 
						count($errors) > 1 ? 's' : '', 
						$href,
						implode("\n", $errors)
					)
				);
			}
			
			$validator->setParameter('', array(
				'agavi.config_path' => $this->path,
				'agavi.environment' => $this->environment,
				'agavi.context' => $this->context,
			));
			
			// run the validation by transforming our document using the generated validation stylesheet
			$result = $validator->transformToDoc($this->doc);
			
			if(libxml_get_last_error() !== false) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = $error->message;
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Schematron validation of configuration file "%s" failed due to the following error%s that occured while validating the document against the schema file "%s": ' . "\n\n%s", 
						$this->path, 
						count($errors) > 1 ? 's' : '', 
						$href,
						implode("\n", $errors)
					)
				);
			}
			
			// validation ran okay, now we need to look at the result document to see if there are errors
			$xpath = new DOMXPath($result);
			$xpath->registerNamespace('svrl', self::SVRL_ISO_NAMESPACE);
			
			$results = $xpath->query('//svrl:failed-assert | //svrl:successful-report');
			if($results->length) {
				// TODO: grab error info from <svrl:failed-assert> and <svrl:successful-report> elements. note that the child <svrl:text> element is optional. also, the <sch:pattern> can have a "name" attribute, but that value never occurs in an SVRL result...
				$errors = array();
				
				libxml_use_internal_errors($luie);
				throw new AgaviParseException(
					sprintf(
						'Schematron validation of configuration file "%s" failed due to the following error%s:' . "\n\n%s",
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
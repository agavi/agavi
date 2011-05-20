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
 * Extended DOMDocument class with several convenience enhancements.
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
class AgaviXmlConfigDomDocument extends DOMDocument
{
	/**
	 * @var        string Default namespace used by several convenience methods in
	 *                    other node classes to access/retrieve elements.
	 */
	protected $defaultNamespaceUri = '';
	
	/**
	 * @var        string XPath prefix of the default namespace defined above.
	 */
	protected $defaultNamespacePrefix = '';
	
	/**
	 * @var        DOMXPath A DOMXPath instance for this document.
	 */
	protected $xpath = null;
	
	/**
	 * @var        array A map of DOM classes and extended Agavi implementations.
	 */
	protected $nodeClassMap = array(
		'DOMAttr'                  => 'AgaviXmlConfigDomAttr',
		'DOMCharacterData'         => 'AgaviXmlConfigDomCharacterData',
		'DOMComment'               => 'AgaviXmlConfigDomComment',
		// yes, even DOMDocument, so we don't get back a vanilla DOMDocument when doing $doc->documentElement etc
		'DOMDocument'              => 'AgaviXmlConfigDomDocument',
		'DOMDocumentFragment'      => 'AgaviXmlConfigDomDocumentFragment',
		'DOMDocumentType'          => 'AgaviXmlConfigDomDocumentType',
		'DOMElement'               => 'AgaviXmlConfigDomElement',
		'DOMEntity'                => 'AgaviXmlConfigDomEntity',
		'DOMEntityReference'       => 'AgaviXmlConfigDomEntityReference',
		'DOMNode'                  => 'AgaviXmlConfigDomNode',
		// 'DOMNotation'              => 'AgaviXmlConfigDomNotation',
		'DOMProcessingInstruction' => 'AgaviXmlConfigDomProcessingInstruction',
		'DOMText'                  => 'AgaviXmlConfigDomText',
	);
	
	/**
	 * The constructor.
	 * Will auto-register Agavi DOM node classes and create an XPath instance.
	 *
	 * @param      string The XML version.
	 * @param      string The XML encoding.
	 *
	 * @see        DOMDocument::__construct()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function __construct($version = "1.0", $encoding = "UTF-8")
	{
		parent::__construct($version, $encoding);
		
		foreach($this->nodeClassMap as $domClass => $agaviClass) {
			$this->registerNodeClass($domClass, $agaviClass);
		}
		
		$this->xpath = new DOMXPath($this);
	}
	
	/**
	 * Load XML from a file.
	 *
	 * @param      string The path to the XML document.
	 * @param      int    Bitwise OR of the libxml option constants.
	 *
	 * @return     bool True of the operation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function load($filename, $options = 0)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::load($filename, $options);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'Error%s occurred while parsing the document: ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		unset($this->xpath);
		$this->xpath = new DOMXPath($this);
		
		if($this->isAgaviConfiguration()) {
			AgaviXmlConfigParser::registerAgaviNamespaces($this);
		}
		
		return $result;
	}
	
	/**
	 * Load XML from a string.
	 *
	 * @param      string The string containing the XML.
	 * @param      int    Bitwise OR of the libxml option constants.
	 *
	 * @return     bool True of the operation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function loadXml($source, $options = 0)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::loadXML($source, $options);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'Error%s occurred while parsing the document: ' . "\n\n%s",
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		unset($this->xpath);
		$this->xpath = new DOMXPath($this);
		
		if($this->isAgaviConfiguration()) {
			AgaviXmlConfigParser::registerAgaviNamespaces($this);
		}
		
		return $result;
	}
	
	/**
	 * Substitutes XIncludes in a DOMDocument object.
	 *
	 * @param      int Bitwise OR of the libxml option constants.
	 *
	 * @return     int The number of XIncludes in the document.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function xinclude($options = 0)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::xinclude($options);
		
		if(libxml_get_last_error() !== false) {
			$throw = false;
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				if($error->level != LIBXML_ERR_WARNING) {
					$throw = true;
				}
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			if($throw) {
				libxml_use_internal_errors($luie);
				throw new DOMException(
					sprintf(
						'Error%s occurred while resolving XInclude directives: ' . "\n\n%s", 
						count($errors) > 1 ? 's' : '', 
						implode("\n", $errors)
					)
				);
			}
		}
		
		libxml_use_internal_errors($luie);
		
		// necessary due to a PHP bug, see http://trac.agavi.org/ticket/621 and http://bugs.php.net/bug.php?id=43364
		if(version_compare(PHP_VERSION, '5.2.6', '<')) {
			$documentUri = $this->documentURI;
			$this->loadXml($this->saveXml());
			$this->documentURI = $documentUri;
		}
		
		return $result;
	}
	
	/**
	 * Import a node into the current document.
	 *
	 * @param      DOMNode The node to import.
	 * @param      bool    Whether or not to recursively import the node's
	 *                     subtree.
	 *
	 * @return     mixed The copied node, or false if it cannot be copied.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function importNode(DOMNode $node, $deep)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::importNode($node, $deep);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'Error%s occurred while importing a new node "%s": ' . "\n\n%s",
					count($errors) > 1 ? 's' : '', 
					$node->nodeName,
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Validate a document based on a schema.
	 *
	 * @param      string The path to the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function schemaValidate($filename)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::schemaValidate($filename)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'XML Schema validation with "%s" failed due to the following error%s: ' . "\n\n%s", 
					$filename, 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Validate a document based on a schema.
	 *
	 * @param      string A string containing the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function schemaValidateSource($source)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::schemaValidateSource($source)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'XML Schema validation failed due to the following error%s: ' . "\n\n%s", 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Perform RELAX NG validation on the document.
	 *
	 * @param      string The path to the schema.
	 *
	 * @return     bool True if the validation is successful; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function relaxNGValidate($filename)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		// gotta do the @ to suppress PHP warnings when the schema cannot be loaded or is invalid
		if(!$result = @parent::relaxNGValidate($filename)) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new DOMException(
				sprintf(
					'RELAX NG validation with "%s" failed due to the following error%s: ' . "\n\n%s",
					$filename,
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		return $result;
	}
	
	/**
	 * Retrieve the DOMXPath instance that is associated with this document.
	 *
	 * @return     DOMXPath The DOMXPath instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getXpath()
	{
		return $this->xpath;
	}
	
	/**
	 * Set a default namespace that should be used when accessing elements via
	 * convenience methods (such as magic get overload for children), and bind it
	 * to the given prefix for use in XPath expressions.
	 *
	 * @param      string A namespace URI
	 * @param      string An optional prefix, defaulting to "_default"
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function setDefaultNamespace($namespaceUri, $prefix = '_default')
	{
		$this->defaultNamespaceUri = $namespaceUri;
		$this->defaultNamespacePrefix = $prefix;
		
		$this->xpath->registerNamespace($prefix, $namespaceUri);
	}
	
	/**
	 * Retrieve the default namespace URI that will be used by node classes, if
	 * set, to conveniently retrieve child elements etc in some methods.
	 *
	 * @return     string A namespace URI.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getDefaultNamespaceUri()
	{
		return $this->defaultNamespaceUri;
	}
	
	/**
	 * Retrieve the default namespace prefix that will be used by node classes, if
	 * set, to conveniently retrieve child elements etc via XPath. 
	 *
	 * @return     string A namespace prefix.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getDefaultNamespacePrefix()
	{
		return $this->defaultNamespacePrefix;
	}
	
	/**
	 * Check whether or not this is a standard Agavi configuration file, i.e. with
	 * a <configurations> and <configuration> envelope.
	 *
	 * @return     bool true, if it is an Agavi config structure, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function isAgaviConfiguration()
	{
		return AgaviXmlConfigParser::isAgaviConfigurationDocument($this);
	}
	
	/**
	 * Retrieve the namespace of the Agavi envelope.
	 *
	 * @return     string A namespace URI, or null if it's not an Agavi config.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getAgaviEnvelopeNamespace()
	{
		if($this->isAgaviConfiguration()) {
			return $this->documentElement->namespaceURI;
		}
	}
	
	/**
	 * Method to retrieve a list of Agavi <configuration> elements regardless of
	 * their namespace.
	 *
	 * @return     array A list of AgaviXmlConfigDomElement elements.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getConfigurationElements()
	{
		$retval = array();
		
		if($this->isAgaviConfiguration()) {
			$agaviNs = $this->getAgaviEnvelopeNamespace();
			
			foreach($this->documentElement->childNodes as $node) {
				if($node->nodeType == XML_ELEMENT_NODE && $node->localName == 'configuration' && $node->namespaceURI == $agaviNs) {
					$retval[] = $node;
				}
			}
		}
		
		return $retval;
	}
	
	/**
	 * Method to retrieve the Agavi <sandbox> element regardless of the namespace.
	 *
	 * @return     AgaviXmlConfigDomElement The <sandbox> element, or null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getSandbox()
	{
		if($this->isAgaviConfiguration()) {
			$agaviNs = $this->getAgaviEnvelopeNamespace();
			
			foreach($this->documentElement->childNodes as $node) {
				if($node->nodeType == XML_ELEMENT_NODE && $node->localName == 'sandbox' && $node->namespaceURI == $agaviNs) {
					return $node;
				}
			}
		}
	}
}

?>
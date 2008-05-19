<?php

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
		$retval = parent::__construct($version, $encoding);
		
		foreach($this->nodeClassMap as $domClass => $agaviClass) {
			$this->registerNodeClass($domClass, $agaviClass);
		}
		
		$this->xpath = new DOMXPath($this);
		
		return $retval;
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
		return AgaviXmlConfigParser::isAgaviEnvelopeNamespace($this->documentElement->namespaceURI);
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
		
		if($this->isAgaviConfigurationFile()) {
			$agaviNs = $this->getAgaviEnvelopeNamespace();
			
			foreach($this->documentElement->childNodes as $configuration) {
				if($configuration->nodeType == XML_ELEMENT_NODE && $configuration->localName == 'configuration' && $configuration->namespaceURI == $this->getAgaviEnvelopeNamespace()) {
					$retval[] = $configuration;
				}
			}
		}
		
		return $retval;
	}
}

?>
<?php

class AgaviXmlConfigDomDocument extends DOMDocument
{
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
		
	public function __construct($version = "1.0", $encoding = "UTF-8")
	{
		$retval = parent::__construct($version, $encoding);
		
		foreach($this->nodeClassMap as $domClass => $agaviClass) {
			$this->registerNodeClass($domClass, $agaviClass);
		}
		
		return $retval;
	}
}

?>
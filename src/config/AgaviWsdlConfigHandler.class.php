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
 * AgaviWsdlConfigHandler simply writes the given WSDL file to disk.
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
class AgaviWsdlConfigHandler extends AgaviXmlConfigHandler
{
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $doc)
	{
		$ro = $this->context->getRouting();
		
		$cleanAppName = preg_replace('/\W/', '', AgaviConfig::get('core.app_name'));
		
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
		$xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
		
		$paramWsdlDefinitionsName     = $ro->getParameter('wsdl_generator[wsdl][definitions][name]', $cleanAppName);
		
		$paramSoapAddressLocation     = $ro->getParameter('wsdl_generator[soap][address][location]');
		                              
		$paramSoapBindingStyle        = $ro->getParameter('wsdl_generator[soap][binding][style]',         'rpc');
		$paramSoapBindingTransport    = $ro->getParameter('wsdl_generator[soap][binding][transport]',     'http://schemas.xmlsoap.org/soap/http');
		
		$paramSoapBodyUse             = $ro->getParameter('wsdl_generator[soap][body][use]',              'literal');
		$paramSoapBodyNamespace       = $ro->getParameter('wsdl_generator[soap][body][namespace]',        /*'urn:' . $paramWsdlDefinitionsName*/ null);
		$paramSoapBodyEncodingStyle   = $ro->getParameter('wsdl_generator[soap][body][encoding_style]',   'http://schemas.xmlsoap.org/soap/encoding/');
		
		$paramSoapHeaderUse           = $ro->getParameter('wsdl_generator[soap][header][use]',            'literal');
		$paramSoapHeaderNamespace     = $ro->getParameter('wsdl_generator[soap][header][namespace]',      /*'urn:' . $paramWsdlDefinitionsName*/ null);
		$paramSoapHeaderEncodingStyle = $ro->getParameter('wsdl_generator[soap][header][encoding_style]', 'http://schemas.xmlsoap.org/soap/encoding/');
		
		$paramSoapFaultUse            = $ro->getParameter('wsdl_generator[soap][fault][use]',             'encoded');
		$paramSoapFaultNamespace      = $ro->getParameter('wsdl_generator[soap][fault][namespace]',       /*'urn:' . $paramWsdlDefinitionsName*/ null);
		$paramSoapFaultEncodingStyle  = $ro->getParameter('wsdl_generator[soap][fault][encoding_style]',  'http://schemas.xmlsoap.org/soap/encoding/');
		
		$paramGlobalRequestHeaders    = $ro->getParameter('wsdl_generator[global_headers][request]',      array());
		$paramGlobalResponseHeaders   = $ro->getParameter('wsdl_generator[global_headers][response]',     array());
		
		$wsdlDefinitions = $xpath->query('/wsdl:definitions');
		foreach($wsdlDefinitions as $wsdlDefinition) {
			$targetNamespaceUri = $wsdlDefinition->getAttribute('targetNamespace');
			$targetNamespacePrefix = $wsdlDefinition->lookupPrefix($targetNamespaceUri);
			
			$wsdlDefinition->setAttribute('name', $paramWsdlDefinitionsName);
			
			$wsdlBindings = $xpath->query('wsdl:binding', $wsdlDefinition);
			foreach($wsdlBindings as $wsdlBinding) {
				$wsdlBinding->setAttribute('name', $paramWsdlDefinitionsName . 'Binding');
				$wsdlBinding->setAttribute('type', $targetNamespacePrefix . ':' . $paramWsdlDefinitionsName . 'PortType');
				
				$soapBindings = $xpath->query('soap:binding', $wsdlBinding);
				foreach($soapBindings as $soapBinding) {
					$soapBinding->setAttribute('style', $paramSoapBindingStyle);
					$soapBinding->setAttribute('transport', $paramSoapBindingTransport);
					
				}
				
				$wsdlOperations = $xpath->query('wsdl:operation', $wsdlBinding);
				foreach($wsdlOperations as $wsdlOperation) {
					
					foreach(array('input' => $paramGlobalRequestHeaders, 'output' => $paramGlobalResponseHeaders) as $target => $headers) {
						foreach($headers as $header) {
							if(!isset($header['namespace'])) {
								$header['namespace'] = $targetNamespaceUri;
							}
							$element = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:header');
							foreach(array('encodingStyle', 'message', 'namespace', 'part', 'use') as $key) {
								if(isset($header[$key])) {
									$element->setAttribute($key, $header[$key]);
								}
							}
							$wsdlOperation->getElementsByTagNameNS('http://schemas.xmlsoap.org/wsdl/', $target)->item(0)->appendChild($element);
						}
					}
					
					if($paramSoapBodyNamespace !== null) {
						$soapOperations = $xpath->query('soap:operation', $wsdlOperation);
						foreach($soapOperations as $soapOperation) {
							$soapOperation->setAttribute('soapAction', $paramSoapBodyNamespace . '#' . $wsdlOperation->getAttribute('name'));
						}
					}
					
					$soapBodies = $xpath->query('.//soap:body', $wsdlOperation);
					foreach($soapBodies as $soapBody) {
						if(!$soapBody->hasAttribute('use')) {
							$soapBody->setAttribute('use', $paramSoapBodyUse);
						}
						if($paramSoapBodyNamespace !== null) {
							$soapBody->setAttribute('namespace', $paramSoapBodyNamespace);
						} elseif($soapBody->getAttribute('use') == 'literal' && $paramSoapBindingStyle == 'document') {
							$soapBody->removeAttribute('namespace');
						}
						if($soapBody->getAttribute('use') == 'encoded') {
							$soapBody->setAttribute('encodingStyle', $paramSoapBodyEncodingStyle);
						}
					}
					
					$soapHeaders = $xpath->query('.//soap:header', $wsdlOperation);
					foreach($soapHeaders as $soapHeader) {
						if(!$soapHeader->hasAttribute('use')) {
							$soapHeader->setAttribute('use', $paramSoapHeaderUse);
						}
						if($paramSoapHeaderNamespace !== null) {
							$soapHeader->setAttribute('namespace', $paramSoapHeaderNamespace);
						} elseif($soapHeader->getAttribute('use') == 'literal' && $paramSoapBindingStyle == 'document') {
							$soapHeader->removeAttribute('namespace');
						}
						if($soapHeader->getAttribute('use') == 'encoded') {
							$soapHeader->setAttribute('encodingStyle', $paramSoapHeaderEncodingStyle);
						}
					}
					
					$soapFaults = $xpath->query('.//soap:fault', $wsdlOperation);
					foreach($soapFaults as $soapFault) {
						if(!$soapFault->hasAttribute('use')) {
							$soapFault->setAttribute('use', $paramSoapFaultUse);
						}
						if($paramSoapFaultNamespace !== null) {
							$soapFault->setAttribute('namespace', $paramSoapFaultNamespace);
						} elseif($soapFault->getAttribute('use') == 'literal' && $paramSoapBindingStyle == 'document') {
							$soapFault->removeAttribute('namespace');
						}
						if($soapFault->getAttribute('use') == 'encoded') {
							$soapFault->setAttribute('encodingStyle', $paramSoapFaultEncodingStyle);
						}
					}
				}
			}
			
			$wsdlPortTypes = $xpath->query('wsdl:portType', $wsdlDefinition);
			foreach($wsdlPortTypes as $wsdlPortType) {
				$wsdlPortType->setAttribute('name', $paramWsdlDefinitionsName . 'PortType');
			}
			
			$wsdlServices = $xpath->query('wsdl:service', $wsdlDefinition);
			foreach($wsdlServices as $wsdlService) {
				$wsdlService->setAttribute('name', $paramWsdlDefinitionsName . 'Service');
				
				$wsdlPorts = $xpath->query('wsdl:port', $wsdlService);
				foreach($wsdlPorts as $wsdlPort) {
					$wsdlPort->setAttribute('name', $paramWsdlDefinitionsName . 'Port');
					$wsdlPort->setAttribute('binding', $targetNamespacePrefix . ':' . $paramWsdlDefinitionsName . 'Binding');
					
					$soapAddresses = $xpath->query('soap:address', $wsdlPort);
					foreach($soapAddresses as $soapAddress) {
						$soapAddress->setAttribute('location', $paramSoapAddressLocation);
					}
				}
			}
		}
		
		return $doc->saveXML();
	}
}

?>
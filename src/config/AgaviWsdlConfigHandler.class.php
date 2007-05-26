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
	 * @param      array An array of DOMDocuments (the config and all parents).
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(array $docs = array())
	{
		if(isset($docs[0])) {
			$doc = $docs[0];
		} else {
			return;
		}
		
		$ro = $this->context->getRouting();
		
		$cleanAppName = preg_replace('/\W/', '', AgaviConfig::get('core.app_name'));
		
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
		$xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
		
		$paramSoapAddressLocation     = $ro->getParameter('wsdl_generator[soap][address][location]');
		                              
		$paramSoapBindingStyle        = $ro->getParameter('wsdl_generator[soap][binding][style]',         'rpc');
		$paramSoapBindingTransport    = $ro->getParameter('wsdl_generator[soap][binding][transport]',     'http://schemas.xmlsoap.org/soap/http');
		
		$paramSoapBodyUse             = $ro->getParameter('wsdl_generator[soap][body][use]',              'literal');
		$paramSoapBodyNamespace       = $ro->getParameter('wsdl_generator[soap][body][namespace]',        /*'urn:' . $cleanAppName*/ null);
		$paramSoapBodyEncodingStyle   = $ro->getParameter('wsdl_generator[soap][body][encoding_style]',   'http://schemas.xmlsoap.org/soap/encoding/');
		
		$paramSoapHeaderUse           = $ro->getParameter('wsdl_generator[soap][header][use]',            'literal');
		$paramSoapHeaderNamespace     = $ro->getParameter('wsdl_generator[soap][header][namespace]',      /*'urn:' . $cleanAppName*/ null);
		$paramSoapHeaderEncodingStyle = $ro->getParameter('wsdl_generator[soap][header][encoding_style]', 'http://schemas.xmlsoap.org/soap/encoding/');
		
		$paramGlobalRequestHeaders    = $ro->getParameter('wsdl_generator[global_headers][request]',      array());
		$paramGlobalResponseHeaders   = $ro->getParameter('wsdl_generator[global_headers][response]',     array());
		
		$wsdlDefinitions = $xpath->query('/wsdl:definitions');
		foreach($wsdlDefinitions as $wsdlDefinition) {
			$targetNamespaceUri = $wsdlDefinition->getAttribute('targetNamespace');
			$targetNamespacePrefix = $wsdlDefinition->lookupPrefix($targetNamespaceUri);
			
			$wsdlDefinition->setAttribute('name', $cleanAppName);
			
			$wsdlBindings = $xpath->query('wsdl:binding', $wsdlDefinition);
			foreach($wsdlBindings as $wsdlBinding) {
				$wsdlBinding->setAttribute('name', $cleanAppName . 'Binding');
				$wsdlBinding->setAttribute('type', $targetNamespacePrefix . ':' . $cleanAppName . 'PortType');
				
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
						}
						if($soapHeader->getAttribute('use') == 'encoded') {
							$soapHeader->setAttribute('encodingStyle', $paramSoapHeaderEncodingStyle);
						}
					}
				}
			}
			
			$wsdlPortTypes = $xpath->query('wsdl:portType', $wsdlDefinition);
			foreach($wsdlPortTypes as $wsdlPortType) {
				$wsdlPortType->setAttribute('name', $cleanAppName . 'PortType');
			}
			
			$wsdlServices = $xpath->query('wsdl:service', $wsdlDefinition);
			foreach($wsdlServices as $wsdlService) {
				$wsdlService->setAttribute('name', $cleanAppName . 'Service');
				
				$wsdlPorts = $xpath->query('wsdl:port', $wsdlService);
				foreach($wsdlPorts as $wsdlPort) {
					$wsdlPort->setAttribute('name', $cleanAppName . 'Port');
					$wsdlPort->setAttribute('binding', $targetNamespacePrefix . ':' . $cleanAppName . 'Binding');
					
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
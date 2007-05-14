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
			$ro = $this->context->getRouting();
			
			$doc = $docs[0];
			
			$cleanAppName = preg_replace('/\W/', '', AgaviConfig::get('core.app_name'));
			
			$xpath = new DOMXPath($doc);
			$xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
			$xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
			
			$paramSoapAddressLocation   = $ro->getParameter('wsdl_generator[soap][address][location]');
			                            
			$paramSoapBindingStyle      = $ro->getParameter('wsdl_generator[soap][binding][style]',       'rpc');
			$paramSoapBindingTransport  = $ro->getParameter('wsdl_generator[soap][binding][transport]',   'http://schemas.xmlsoap.org/soap/http');
			                            
			$paramSoapBodyUse           = $ro->getParameter('wsdl_generator[soap][body][use]',            'literal');
			$paramSoapBodyNamespace     = $ro->getParameter('wsdl_generator[soap][body][namespace]',      'urn:' . $cleanAppName);
			$paramSoapBodyEncodingStyle = $ro->getParameter('wsdl_generator[soap][body][encoding_style]', 'http://schemas.xmlsoap.org/soap/encoding/');
			                            
			$paramTargetNamespaceUri    = $ro->getParameter('wsdl_generator[target_namespace][uri]',      'urn:' . $cleanAppName);
			$paramTargetNamespacePrefix = $ro->getParameter('wsdl_generator[target_namespace][prefix]',   'tns');
			
			$wsdlDefinitions = $xpath->query('/wsdl:definitions');
			foreach($wsdlDefinitions as $wsdlDefinition) {
				$wsdlDefinition->setAttribute('name', $cleanAppName);
				$wsdlDefinition->setAttribute('targetNamespace', $paramTargetNamespaceUri);
				$wsdlDefinition->setAttribute('xmlns:' . $paramTargetNamespacePrefix, $paramTargetNamespaceUri);
				
				$wsdlBindings = $xpath->query('wsdl:binding', $wsdlDefinition);
				foreach($wsdlBindings as $wsdlBinding) {
					$wsdlBinding->setAttribute('name', $cleanAppName . 'Binding');
					$wsdlBinding->setAttribute('type', $paramTargetNamespacePrefix . ':' . $cleanAppName . 'PortType');
					
					$soapBindings = $xpath->query('soap:binding', $wsdlBinding);
					foreach($soapBindings as $soapBinding) {
						$soapBinding->setAttribute('style', $paramSoapBindingStyle);
						$soapBinding->setAttribute('transport', $paramSoapBindingTransport);
						
					}
					
					$wsdlOperations = $xpath->query('wsdl:operation', $wsdlBinding);
					foreach($wsdlOperations as $wsdlOperation) {
						$soapOperations = $xpath->query('soap:operation', $wsdlOperation);
						foreach($soapOperations as $soapOperation) {
							$soapOperation->setAttribute('soapAction', $paramSoapBodyNamespace . '#' . $wsdlOperation->getAttribute('name'));
						}
						
						$soapBodies = $xpath->query('.//soap:body', $wsdlOperation);
						foreach($soapBodies as $soapBody) {
							$soapBody->setAttribute('use', $paramSoapBodyUse);
							$soapBody->setAttribute('namespace', $paramSoapBodyNamespace);
							$soapBody->setAttribute('encodingStyle', $paramSoapBodyEncodingStyle);
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
						$wsdlPort->setAttribute('binding', $paramTargetNamespacePrefix . ':' . $cleanAppName . 'Binding');
						
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
}

?>
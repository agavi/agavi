<?php

class Default_SearchEngineSpamErrorView extends AgaviSampleAppDefaultBaseView
{
	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		return $this->createForwardContainer(AgaviConfig::get('actions.error_404_module'), AgaviConfig::get('actions.error_404_action'));
	}

	/**
	 * Execute any presentation logic for SOAP requests.
	 */
	public function executeSoap(AgaviRequestDataHolder $rd)
	{
		return new SoapFault('Error 101', 'Unknown Product "' . $this->getAttribute('product_name') . '"');
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviRequestDataHolder $rd)
	{
		return array('faultCode' => 101, 'faultString' => 'Unknown Product "' . $this->getAttribute('product_name') . '"');
	}
}

?>
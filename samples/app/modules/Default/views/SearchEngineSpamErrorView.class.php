<?php

class Default_SearchEngineSpamErrorView extends AgaviSampleAppDefaultBaseView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		return array(AgaviConfig::get('actions.error_404_module'), AgaviConfig::get('actions.error_404_action'));
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviRequestDataHolder $rd)
	{
		$this->getResponse()->setContent(array('faultCode' => 101, 'faultString' => 'Unknown Product "' . $this->getAttribute('product_name') . '"'));
	}
}

?>
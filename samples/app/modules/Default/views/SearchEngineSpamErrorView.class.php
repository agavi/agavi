<?php

class Default_SearchEngineSpamErrorView extends AgaviSampleAppDefaultBaseView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $r)
	{
		return array(AgaviConfig::get('errors.404_module'), AgaviConfig::get('errors.404_action'));
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviRequestDataHolder $r)
	{
		$this->getResponse()->setContent(array('faultCode' => 101, 'faultString' => 'Unknown Product "' . $this->getAttribute('product_name') . '"'));
	}
}

?>
<?php

class Default_SearchEngineSpamErrorView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		return array(AgaviConfig::get('errors.404_module'), AgaviConfig::get('errors.404_action'));
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviParameterHolder $parameters)
	{
		$this->getResponse()->setContent(array('faultCode' => 101, 'faultString' => 'Unknown Product "' . $this->getAttribute('product_name') . '"'));
	}
}

?>
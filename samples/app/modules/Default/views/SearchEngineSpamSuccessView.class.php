<?php

class Default_SearchEngineSpamSuccessView extends AgaviSampleAppDefaultBaseView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Congratulations!', 'default.SearchEngineSpam'));
	}

	/**
	 * Execute any presentation logic for SOAP requests.
	 */
	public function executeSoap(AgaviRequestDataHolder $rd)
	{
		return $this->getAttribute('product_price');
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviRequestDataHolder $rd)
	{
		return array($this->getAttribute('product_price'));
	}
}

?>
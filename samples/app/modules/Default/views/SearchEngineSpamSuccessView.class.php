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
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Congratulations!', 'default.SearchEngineSpam'));
	}

	public function executeText(AgaviRequestDataHolder $rd)
	{
		return $this->getAttribute('product_price');
	}

	/**
	 * Execute any presentation logic for JSON requests.
	 */
	public function executeJson(AgaviRequestDataHolder $rd)
	{
		return json_encode(
			array(
				'product_price' => $this->getAttribute('product_price'),
			)
		);
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
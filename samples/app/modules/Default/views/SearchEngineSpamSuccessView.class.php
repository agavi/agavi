<?php

class Default_SearchEngineSpamSuccessView extends AgaviSampleAppDefaultBaseView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $r)
	{
		parent::executeHtml($r);
		
		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Congratulations!', 'default.SearchEngineSpam'));
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviRequestDataHolder $r)
	{
		$this->getResponse()->setContent(array($this->getAttribute('product_price')));
	}
}

?>
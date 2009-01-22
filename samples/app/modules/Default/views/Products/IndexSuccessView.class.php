<?php

class Default_Products_IndexSuccessView extends AgaviSampleAppDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		
		// set the title
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Our Fine Products', 'default.SearchEngineSpam'));
	}

	// public function executeText(AgaviRequestDataHolder $rd)
	// {
	// 	return $this->getAttribute('product_price');
	// }
	// 
	// public function executeSoap(AgaviRequestDataHolder $rd)
	// {
	// 	return $this->getAttribute('product_price');
	// }
	// 
	// public function executeXmlrpc(AgaviRequestDataHolder $rd)
	// {
	// 	return array($this->getAttribute('product_price'));
	// }
}

?>

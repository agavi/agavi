<?php

class Default_Products_IndexSuccessView extends AgaviSampleAppDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		
		// set the title
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Our Fine Products', 'default.SearchEngineSpam'));
	}

	public function executeText(AgaviRequestDataHolder $rd)
	{
		$products = $this->getAttribute('products');
		
		$ret = array();
		$ret[] = sprintf('+%\'-32s+%\'-12s+', '', '');
		$ret[] = sprintf('| %-30s | %-10s |', 'Name:', 'Price:');
		$ret[] = sprintf('+%\'-32s+%\'-12s+', '', '');
		
		foreach($products as $product) {
			$ret[] = sprintf('| %-30s | %10.2f |', $product->getName(), $product->getPrice());
			$ret[] = sprintf('+%\'-32s+%\'-12s+', '', '');
		}
		
		return implode(PHP_EOL, $ret);
	}
	
	public function executeSoap(AgaviRequestDataHolder $rd)
	{
		return $this->getAttribute('products');
	}
	// 
	// public function executeXmlrpc(AgaviRequestDataHolder $rd)
	// {
	// 	return array($this->getAttribute('product_price'));
	// }
}

?>

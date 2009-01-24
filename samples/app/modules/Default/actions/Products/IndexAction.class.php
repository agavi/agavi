<?php

class Default_Products_IndexAction extends AgaviSampleAppDefaultBaseAction
{
	public function execute(AgaviRequestDataHolder $rd)
	{
		$products = $this->getContext()->getModel('ProductFinder')->retrieveAll();
		
		$this->setAttribute('products', $products);
		
		return 'Success';
	}
}

?>
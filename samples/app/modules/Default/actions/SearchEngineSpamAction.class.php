<?php

class Default_SearchEngineSpamAction extends AgaviSampleAppDefaultBaseAction
{
	public function executeRead(AgaviRequestDataHolder $r)
	{
		$this->setAttribute('product_name', $r->getParameter('name'));
		$price = $this->getContext()->getModel('AgaviSampleAppPriceFinder', 'Default')->getPriceByProductName($r->getParameter('name'));
		if($price !== null) {
			$this->setAttribute('product_price', $price);
			return 'Success';
		} else {
			return 'Error';
		}
	}
}

?>
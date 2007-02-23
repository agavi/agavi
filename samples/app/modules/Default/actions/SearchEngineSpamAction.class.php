<?php

class Default_SearchEngineSpamAction extends AgaviSampleAppDefaultBaseAction
{
	public function executeRead(AgaviRequestDataHolder $rd)
	{
		$this->setAttribute('product_name', $rd->getParameter('name'));
		$price = $this->getContext()->getModel('AgaviSampleAppPriceFinder', 'Default')->getPriceByProductName($rd->getParameter('name'));
		if($price !== null) {
			$this->setAttribute('product_price', $price);
			return 'Success';
		} else {
			return 'Error';
		}
	}
}

?>
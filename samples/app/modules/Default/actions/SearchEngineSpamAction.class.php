<?php

class Default_SearchEngineSpamAction extends AgaviAction
{
	public function executeRead(AgaviParameterHolder $parameters)
	{
		$this->setAttribute('product_name', $parameters->getParameter('name'));
		$price = $this->getContext()->getModel('AgaviSampleAppPriceFinder', 'Default')->getPriceByProductName($parameters->getParameter('name'));
		if($price !== null) {
			$this->setAttribute('product_price', $price);
			return 'Success';
		} else {
			return 'Error';
		}
	}
}

?>
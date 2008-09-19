<?php

class Default_SearchEngineSpamAction extends AgaviSampleAppDefaultBaseAction
{
	public function executeRead(AgaviRequestDataHolder $rd)
	{
		$pfm = $this->getContext()->getModel('PriceFinder', 'Default');
		$id = $rd->getParameter('id');
		
		// was the name in the url? then validate that, too
		if($rd->hasParameter('product_name')) {
			$name = $rd->getParameter('name');
			$price = $pfm->getPriceByProductInfo($id, $name);
		} else {
			$name = $pfm->getNameByProductId($id);
			$price = $pfm->getPriceByProductId($id);
		}
		if($price !== null) {
			$this->setAttribute('product_id', $id);
			$this->setAttribute('product_name', $name);
			$this->setAttribute('product_price', $price);
			return 'Success';
		} else {
			return 'Error';
		}
	}
}

?>
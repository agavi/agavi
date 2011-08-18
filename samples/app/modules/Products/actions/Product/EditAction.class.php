<?php

class Products_Product_EditAction extends AgaviSampleAppProductsBaseAction
{
	public function executeRead(AgaviRequestDataHolder $rd)
	{
		// the validator already pulled the product object from the database and put it into the request data
		// so there's not much we need to do here
		$this->setAttribute('product', $rd->getParameter('product'));
		
		return 'Input';
	}
	
	public function executeWrite(AgaviRequestDataHolder $rd)
	{
		// not implemented
		throw new Exception('Not implemented');
	}
	
	public function checkPermissions(AgaviUser $us, AgaviRequestDataHolder $rd)
	{
		return $us->isAllowed($rd->getParameter('product'), 'write');
	}
	
	public function isSecure()
	{
		return true;
	}
	
	public function getCredentials()
	{
		return 'product.write';
	}
}

?>
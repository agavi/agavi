<?php

class ProductFinderModel extends AgaviSampleAppBaseModel implements AgaviISingletonModel
{
	// imagine this stuff is in a database :)
	protected static $products = array(
		array(
			'id'    => 8172401,
			'name'  => 'TPS Report Cover Sheet',
			'price' => 0.89,
		),
		array(
			'id'    => 917246,
			'name'  => 'Weighted Companion Cube',
			'price' => 129.99,
		),
		array(
			'id'    => 7856122,
			'name'  => 'Longcat',
			'price' => 14599,
		),
		array(
			'id'    => 123456,
			'name'  => 'Red Stapler',
			'price' => 3.14,
		),
		array(
			'id'    => 3165463,
			'name'  => 'Sildenafil Citrate',
			'price' => 14.69,
		),
	);
	
	public function retrieveAll()
	{
		$retval = array();
		
		foreach(self::$products as $product) {
			$retval[] = $this->context->getModel('Product', null, array($product));
		}
		
		return $retval;
	}
	
	public function retrieveRandom()
	{
		return $this->context->getModel('Product', null, array(self::$products[array_rand(self::$products)]));
	}
	
	public function retrieveByName($productName)
	{
		foreach(self::$products as $product) {
			if($product['name'] == $productName) {
				return $this->context->getModel('Product', null, array($product));
			}
		}
	}
	
	public function retrieveById($productId)
	{
		foreach(self::$products as $product) {
			if($product['id'] == $productId) {
				return $this->context->getModel('Product', null, array($product));
			}
		}
	}
	
	public function retrieveByIdAndName($productId, $productName)
	{
		foreach(self::$products as $product) {
			if($product['id'] == $productId && $product['name'] == $productName) {
				return $this->context->getModel('Product', null, array($product));
			}
		}
	}
}

?>
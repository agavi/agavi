<?php

class ProductFinderModel extends AgaviSampleAppBaseModel implements AgaviISingletonModel
{
	// imagine this stuff is in a database :)
	protected static $products = array(
		array(
			'id'    => 8172401,
			'name'  => 'brains',
			'price' => 0.89,
		),
		array(
			'id'    => 917246,
			'name'  => 'chainsaws',
			'price' => 129.99,
		),
		array(
			'id'    => 7856122,
			'name'  => 'mad coding skills',
			'price' => 14599,
		),
		array(
			'id'    => 123456,
			'name'  => 'nonsense',
			'price' => 3.14,
		),
		array(
			'id'    => 3165463,
			'name'  => 'viagra',
			'price' => 14.69,
		),
	);
	
	public function retrieveAll()
	{
		$retval = array();
		
		foreach(self::$products as $product) {
			$retval[] = $this->context->getModel('Product', null, array($product));
		}
		return self::$products;
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
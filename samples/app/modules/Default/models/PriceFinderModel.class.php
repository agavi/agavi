<?php

class Default_PriceFinderModel extends AgaviSampleAppDefaultBaseModel implements AgaviISingletonModel
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
	
	public function getProducts()
	{
		return self::$products;
	}
	
	public function getPriceByProductName($productName)
	{
		foreach(self::$products as $product) {
			if($product['name'] == $productName) {
				return $product['price'];
			}
		}
	}
	
	public function getPriceByProductId($productId)
	{
		foreach(self::$products as $product) {
			if($product['id'] == $productId) {
				return $product['price'];
			}
		}
	}
	
	public function getNameByProductId($productId)
	{
		foreach(self::$products as $product) {
			if($product['id'] == $productId) {
				return $product['name'];
			}
		}
	}
	
	public function getPriceByProductInfo($productId, $productName)
	{
		foreach(self::$products as $product) {
			if($product['id'] == $productId && $product['name'] == $productName) {
				return $product['price'];
			}
		}
	}
}

?>
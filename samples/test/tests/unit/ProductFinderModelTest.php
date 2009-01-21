<?php 

class ProductFinderModelTest extends AgaviUnitTestCase
{
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
	
	/**
	 * @dataProvider productNamePrices
	 */
	public function testValidProductPricesByName($productName, $price)
	{
		$finder = $this->getContext()->getModel('ProductFinder', 'Default');
		$this->assertEquals($price, $finder->retrieveByName($productName)->getPrice());
	}
	
	public function productNamePrices()
	{
		$retval = array();
		foreach(self::$products as $product) {
			$retval[$product['name']] = array(
				$product['name'],
				$product['price'],
			);
		}
		return $retval;
	}
	
	/**
	 * @dataProvider productIdPrices
	 */
	public function testValidProductPricesById($productId, $price)
	{
		$finder = $this->getContext()->getModel('ProductFinder', 'Default');
		$this->assertEquals($price, $finder->retrieveById($productId)->getPrice());
	}
	
	public function productIdPrices()
	{
		$retval = array();
		foreach(self::$products as $product) {
			$retval[$product['name']] = array(
				$product['id'],
				$product['price'],
			);
		}
		return $retval;
	}
	
	/**
	 * @dataProvider productInfoPrices
	 */
	public function testValidProductPricesByInfo($productId, $productName, $price)
	{
		$finder = $this->getContext()->getModel('ProductFinder', 'Default');
		$this->assertEquals($price, $finder->retrieveByIdAndName($productId, $productName)->getPrice());
	}
	
	public function productInfoPrices()
	{
		$retval = array();
		foreach(self::$products as $product) {
			$retval[$product['name']] = array(
				$product['id'],
				$product['name'],
				$product['price'],
			);
		}
		return $retval;
	}
	
	public function testNullForUnknownProductName()
	{
		$this->assertNull($this->getContext()->getModel('ProductFinder', 'Default')->retrieveByName('unknown product'));
	}
	
	public function testNullForUnknownProductId()
	{
		$this->assertNull($this->getContext()->getModel('ProductFinder', 'Default')->retrieveById(-1));
	}
	
	public function testNullForUnknownProductInfo()
	{
		$this->assertNull($this->getContext()->getModel('ProductFinder', 'Default')->retrieveByIdAndName(-1, 'unknown product'));
	}
	
	public function testNullForPartiallyValidProductInfo()
	{
		$this->assertNull($this->getContext()->getModel('ProductFinder', 'Default')->retrieveByIdAndName(123456, 'nonsenseZOMG'));
		$this->assertNull($this->getContext()->getModel('ProductFinder', 'Default')->retrieveByIdAndName(1234567, 'nonsense'));
	}
}

?>
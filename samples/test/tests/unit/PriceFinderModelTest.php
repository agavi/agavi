<?php 


class PriceFinderModelTest extends AgaviUnitTestCase
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
		$finder = $this->getContext()->getModel('PriceFinder', 'Default');
		$this->assertEquals($price, $finder->getPriceByProductName($productName));
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
		$finder = $this->getContext()->getModel('PriceFinder', 'Default');
		$this->assertEquals($price, $finder->getPriceByProductId($productId));
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
		$finder = $this->getContext()->getModel('PriceFinder', 'Default');
		$this->assertEquals($price, $finder->getPriceByProductInfo($productId, $productName));
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
	
	public function testPriceNullForUnknownProductName()
	{
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductName('unknown product'));
	}
	
	public function testPriceNullForUnknownProductId()
	{
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductId(-1));
	}
	
	public function testPriceNullForUnknownProductInfo()
	{
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductInfo(-1, 'unknown product'));
	}
	
	public function testPriceNullForPartiallyValidProductInfo()
	{
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductInfo(123456, 'nonsenseZOMG'));
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductInfo(1234567, 'nonsense'));
	}
}

?>
<?php 


class PriceFinderModelTest extends AgaviUnitTestCase
{
	/**
	 * @dataProvider productPrices
	 */
	public function testValidProductPrices($productName, $price)
	{
		$finder = $this->getContext()->getModel('PriceFinder', 'Default');
		$this->assertEquals($price, $finder->getPriceByProductName($productName));
	}
	
	public function productPrices()
	{
		return array(	'brains' 	=> array('brains', 0.89),
						'chainsaws' => array('chainsaws', 129.99),
						'coding'	=> array('mad coding skills', 14599),
						'nonsense'	=> array('nonsense', 3.14),
						'viagra'	=> array('viagra', 14.69));
	}
	
	public function testPriceNullForUnknownProduct()
    {
		$this->assertNull($this->getContext()->getModel('PriceFinder', 'Default')->getPriceByProductName('unknown product'));
	}

}

?>
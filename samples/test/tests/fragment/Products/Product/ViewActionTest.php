<?php 

class Products_Product_ViewActionTest extends AgaviActionTestCase
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
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'Products/Product/View';
		$this->moduleName = 'Default';
	}
	
	/**
	 * @dataProvider successViewValidProductsData
	 */
	public function testSuccessViewValidProducts($parameters, $price)
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $parameters)));
		$this->runAction();
		$this->assertValidatedArgument('id');
		$this->assertViewNameEquals('Success');
		$this->assertViewModuleNameEquals('Default');
		$this->assertContainerAttributeExists('product');
		$this->assertEquals($price, $this->getAttribute('product')->getPrice());
	}
	
	public function successViewValidProductsData()
	{
		$retval = array();
		foreach(self::$products as $product) {
			$retval['id only: ' . $product['id']] = array(array('id' => $product['id']), $product['price']);
		}
		foreach(self::$products as $product) {
			$retval['id+name: ' . $product['id'] . '/' . $product['name']] = array(array('id' => $product['id'], 'name' => $product['name']), $product['price']);
		}
		return $retval;
	}
	
	/**
	 * @dataProvider errorViewInvalidProductsData
	 */
	public function testErrorViewInvalidProducts($parameters)
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $parameters)));
		$this->runAction();
		$this->assertValidatedArgument('id');
		$this->assertViewNameEquals('Error');
		$this->assertViewModuleNameEquals('Default');
	}
	
	public function testErrorViewFailedProductValidation()
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('id' => ''))));
		$this->runAction();
		$this->assertValidatedArgument('id');
		$this->assertFailedArgument('id');
		$this->assertViewNameEquals('Error');
		$this->assertViewModuleNameEquals('Default');
	}
	
	public function errorViewInvalidProductsData()
	{
		return array(
			'only product name given' => array(array('name' => 'nonsense')),
			'invalid product id given' => array(array('id' => 81236123)),
			'negative product id given' => array(array('id' => -1)),
			'id and name given, id invalid' => array(array('id' => 123457, 'name' => 'nonsense')),
			'id and name given, name invalid' => array(array('id' => 123456, 'name' => 'nonsenseZOMG')),
			'id and name given, both invalid' => array(array('id' => -1, 'name' => 'nonsenseZOMG')),
		);
	}
	
	public function testIsNotSimple()
	{
		$this->assertIsNotSimple();
	}
	
	public function testDefaultView()
	{
		$this->assertDefaultView('Input');
	}
	
	public function testReadMethod()
	{
		$this->assertHandlesMethod('read');
	}
	
	public function testWriteMethod()
	{
		$this->assertNotHandlesMethod('write');
	}
	
	public function products()
	{
		
		return array(
			'brains'    => array('brains'),
			'chainsaws' => array('chainsaws'),
			'coding'    => array('mad coding skills'),
			'nonsense'  => array('nonsense'),
			'viagra'    => array('viagra'),
		);
	}
}

?>
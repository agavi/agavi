<?php 

class Products_Product_ViewActionTest extends AgaviActionTestCase
{
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
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'Product.View';
		$this->moduleName = 'Products';
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
		$this->assertViewModuleNameEquals('Products');
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
		$this->assertViewModuleNameEquals('Products');
	}
	
	public function testErrorViewFailedProductValidation()
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('id' => ''))));
		$this->runAction();
		$this->assertValidatedArgument('id');
		$this->assertFailedArgument('id');
		$this->assertViewNameEquals('Error');
		$this->assertViewModuleNameEquals('Products');
	}
	
	public function errorViewInvalidProductsData()
	{
		return array(
			'only product name given' => array(array('name' => 'Red Stapler')),
			'invalid product id given' => array(array('id' => 81236123)),
			'negative product id given' => array(array('id' => -1)),
			'id and name given, id invalid' => array(array('id' => 123457, 'name' => 'Red Stapler')),
			'id and name given, name invalid' => array(array('id' => 123456, 'name' => 'Red StaplerZOMG')),
			'id and name given, both invalid' => array(array('id' => -1, 'name' => 'Red StaplerZOMG')),
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
}

?>
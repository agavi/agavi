<?php 

class SearchEngineSpamActionTest extends AgaviActionTestCase
{
	
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'SearchEngineSpam';
		$this->moduleName = 'Default';
		
	}
	
	/**
	 * @dataProvider products
	 */
	public function testSuccessViewValidProducts($productName)
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('name' => $productName))));
		$this->runAction();
		$this->assertViewNameEquals('Success');
	}
	
	public function testErrorViewInvalidProduct()
	{
		$this->setRequestMethod('read');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('name' => 'nonexistant product'))));
		$this->runAction();
		$this->assertViewNameEquals('Error');
	}
	
	public function testIsNotSimple()
	{
		$this->assertIsNotSimple();
	}
	
	public function testDefaultViewName()
	{
		$this->assertDefaultViewName('Input');
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
		return array(	'brains' 	=> array('brains'),
						'chainsaws' => array('chainsaws'),
						'coding'	=> array('mad coding skills'),
						'nonsense'	=> array('nonsense'),
						'viagra'	=> array('viagra'));
	}


}

?>
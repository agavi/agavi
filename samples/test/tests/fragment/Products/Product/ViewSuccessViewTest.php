<?php 

class Products_Product_ViewSuccessViewTest extends AgaviViewTestCase
{

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// FIXME: the underlying issue must be solved
		$this->actionName = 'Product.View';
		$this->moduleName = 'Products';
		$this->viewName   = 'Success';
	}
	
	/**
	 * @dataProvider supportedOtProvider
	 */
	public function testHandlesOutputType($ot_name)
	{
		$this->assertHandlesOutputType($ot_name);
	}
	
	public function supportedOtProvider()
	{
		return array(
			'html'   => array('html'),
			'html'   => array('text'),
			// 'json'   => array('json'),
			'soap'   => array('soap'),
			'xmlrpc' => array('xmlrpc'),
		);
	}
	
	public function testNotHandlesXmlOutputType()
	{
		$this->assertNotHandlesOutputType('xml');
	}
	
	// FIXME: needs to be updated
	public function testResponseHtml()
	{		
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('product_name' => 'spam'))));

		$this->setAttribute('product_id', 1234);
		$this->setAttribute('product_name', 'spam');
		$this->setAttribute('product_price', '123.45');
		$this->runView();
		$this->assertResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertResponseHasNoRedirect();
		$this->assertContainerAttributeExists('_title');
	}
	
	// public function testResponseJson()
	// {		
	// 	$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('product_name' => 'spam'))));
	// 
	// 	$this->setAttribute('product_id', 1234);
	// 	$this->setAttribute('product_name', 'spam');
	// 	$this->setAttribute('product_price', '123.45');
	// 	$this->runView('json');
	// 	$this->assertResponseHasHTTPStatus(200);
	// 	$this->assertViewResultEquals('{"product_price":"123.45"}');
	// 	$this->assertResponseHasNoRedirect();
	// }
}

?>
<?php 

class SearchEngineSpamSuccessViewTest extends AgaviViewTestCase
{

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'SearchEngineSpam';
		$this->moduleName = 'Default';
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
			'json'   => array('json'),
			'soap'   => array('soap'),
			'xmlrpc' => array('xmlrpc'),
		);
	}
	
	public function testNotHandlesXmlOutputType()
	{
		$this->assertNotHandlesOutputType('xml');
	}
	
	public function testResultLayers()
	{		
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('product_name' => 'spam'))));

		$this->setAttribute('product_id', 1234);
		$this->setAttribute('product_name', 'spam');
		$this->setAttribute('product_price', '123.45');
		$this->runView();
		$this->assertHasLayer('content');
	}
}

?>
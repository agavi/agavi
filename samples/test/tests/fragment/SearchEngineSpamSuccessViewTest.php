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
}

?>
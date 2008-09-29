<?php 

class LoginSuccessViewTest extends AgaviViewTestCase
{

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'Login';
		$this->moduleName = 'Default';
		$this->viewName   = 'Success';
	}
	
	public function testHandlesOutputType()
	{
		$this->assertHandlesOutputType('html');
	}
	
	public function testResponseRedirect()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'foo', 'password' => 'bar'))));
		$this->getContext()->getUser()->setAttribute('redirect', 'http://www.example.com/', 'org.agavi.SampleApp.login');
		$this->runView();
		$this->assertViewResultEquals('');
		$this->assertResponseRedirectsTo(array('code' => '302', 'location' => 'http://www.example.com/'));
	}
	
	public function testResponseHtml()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'foo', 'password' => 'bar'))));
		$this->runView();
		$this->assertResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertResponseHasNoRedirect();
		$this->assertContainerAttributeExists('title');
	}
}

?>
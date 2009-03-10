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
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick'))));
		$this->getContext()->getUser()->setAttribute('redirect', 'http://www.example.com/', 'org.agavi.SampleApp.login');
		$this->runView();
		$this->assertViewResultEquals('');
		$this->assertResponseRedirectsTo(array('code' => '302', 'location' => 'http://www.example.com/'));
	}
	
	public function testResponseHtml()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick'))));
		$this->runView();
		$this->assertResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertResponseHasNoRedirect();
		$this->assertContainerAttributeExists('_title');
	}
	
	public function testResponseHasCookiesWhenRememberSet()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick', 'remember' => true))));
		$this->runView();
		$this->assertResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertResponseHasNoRedirect();
		$this->assertContainerAttributeExists('_title');
		$this->assertResponseHasCookie('autologon[username]', array('value' => 'Chuck Norris', 'lifetime' => '+14 days', 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null));
		$this->assertResponseHasCookie('autologon[password]', array('value' => 'd436130cf2f5024cfdb3aa7325322d530336b95f', 'lifetime' => '+14 days', 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null));
	}
	
}

?>
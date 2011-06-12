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
		$this->assertViewRedirectsTo(array('code' => '302', 'location' => 'http://www.example.com/'));
	}
	
	public function testResponseHtml()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick'))));
		$this->runView();
		$this->assertViewResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertViewRedirectsNot();
		$this->assertContainerAttributeExists('_title');
	}
	
	public function testResponseHasCookiesWhenRememberSet()
	{
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick', 'remember' => true))));
		$this->runView();
		$this->assertViewResponseHasHTTPStatus(200);
		$this->assertViewResultEquals('');
		$this->assertHasLayer('content');
		$this->assertHasLayer('decorator');
		$this->assertViewRedirectsNot();
		$this->assertContainerAttributeExists('_title');
		$this->assertViewSetsCookie('autologon[username]', array('value' => 'Chuck Norris', 'lifetime' => '+14 days', 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null));
		$this->assertViewSetsCookie('autologon[password]', array('value' => '$2a$10$2/Gmc4XpwAytFgy3wfrW9OUnkzd6ahgcMqrm4cEc4zD3IFD1GB6IG', 'lifetime' => '+14 days', 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null));
	}
	
}

?>
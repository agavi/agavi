<?php 

class LoginFlowTest extends AgaviFlowTestCase
{
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'Login';
		$this->moduleName = 'Default';
	}
	
	public function testValidWriteRequest()
	{
		$this->setRequestMethod('write');
		$this->setArguments($this->createRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('username' => 'Chuck Norris', 'password' => 'kick'))));
		$this->dispatch();
		$this->assertResponseHasTag(array('tag' => 'body'));
		$this->assertResponseHasTag(array('tag' => 'h2', 'content' => 'Login Successful'));
	}
	
}

?>
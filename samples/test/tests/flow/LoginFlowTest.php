<?php 

class LoginFlowTest extends AgaviFlowTestCase
{
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->actionName = 'Login';
		$this->moduleName = 'Default';
		$this->input = '/en/auth/login';
	}
	
	public function testValidWriteRequest()
	{
		$this->dispatch(array('username' => 'Chuck Norris', 'password' => 'kick'), null, 'write');
		$this->assertResponseHasTag(array('tag' => 'body'));
		$this->assertResponseHasTag(array('tag' => 'h2', 'content' => 'Login Successful'));
	}
	
	public function testInvalidWriteRequest()
	{
		$this->dispatch(array('username' => 'Chuck Norris', 'password' => 'foo'), null, 'write');
		$this->assertResponseHasTag(array('tag' => 'body'));
		$this->assertResponseHasNotTag(array('tag' => 'h2', 'content' => 'Login Successful'));
		$this->assertResponseHasTag(array('tag' => 'p', 'content' => 'Wrong Password'));
	}
}

?>
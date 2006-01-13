<?php
if (!defined('AG_USER_NAMESPACE')) {
	define('AG_USER_NAMESPACE', 'org/agavi');
}	

class SampleSecurityUser extends BasicSecurityUser
{
	public function initialize($context, $parameters=null)
	{
		$this->context = $context;
		if ($parameters != null) {
			$this->parameters = array_merge($this->parameters, $parameters);
		}
		$this->attributes = array();
	}
}

class BasicSecurityUserTest extends UnitTestCase
{
	private $_u = null;

	public function setUp()
	{
		$this->_u = new SampleSecurityUser();
		$context = Context::getInstance();
		$this->_u->initialize($context);
	}

	public function testaddCredential()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential('test1');
		$this->assertTrue($this->_u->hasCredential('test1'));
		$this->_u->addCredential('test2');
		$this->assertTrue($this->_u->hasCredential('test2'));
	}
	
	public function testhasCredential()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential('test1');
		$this->_u->addCredential('test2');
		$this->_u->addCredential('test3');
		$this->_u->addCredential('test4');
		$this->assertTrue($this->_u->hasCredential('test1'));
		$this->assertTrue($this->_u->hasCredential(array('test2', 'test3')));
		$this->assertTrue($this->_u->hasCredential(array('test1', array('test2', 'test3'))));
		$this->assertTrue($this->_u->hasCredential(array('test1', array('test2', 'test5'))));
		$this->assertFalse($this->_u->hasCredential('test5'));
		$this->assertFalse($this->_u->hasCredential(array('test2', 'test5')));
		$this->assertFalse($this->_u->hasCredential(array('test5', array('test2', 'test3'))));
		$this->assertFalse($this->_u->hasCredential(array('test1', array('test5', 'test6'))));
	}
	
	public function teststrictCredentialComparison()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential("0");
		$this->assertTrue($this->_u->hasCredential("0"));
		$this->assertFalse($this->_u->hasCredential(0));
		$this->assertFalse($this->_u->hasCredential(false));
	}

}
?>
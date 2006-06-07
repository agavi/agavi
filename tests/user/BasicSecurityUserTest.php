<?php
class SampleSecurityUser extends BasicSecurityUser
{
	public function initialize(AgaviContext $context, $parameters=null)
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
		$this->assertTrue($this->_u->hasCredentials('test1'));
		$this->_u->addCredential('test2');
		$this->assertTrue($this->_u->hasCredentials('test2'));
	}
	
	public function testhasCredentials()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential('test1');
		$this->_u->addCredential('test2');
		$this->_u->addCredential('test3');
		$this->_u->addCredential('test4');
		$this->assertTrue($this->_u->hasCredentials('test1'));
		$this->assertTrue($this->_u->hasCredentials(array('test2', 'test3')));
		$this->assertTrue($this->_u->hasCredentials(array('test1', array('test2', 'test3'))));
		$this->assertTrue($this->_u->hasCredentials(array('test1', array('test2', 'test5'))));
		$this->assertFalse($this->_u->hasCredentials('test5'));
		$this->assertFalse($this->_u->hasCredentials(array('test2', 'test5')));
		$this->assertFalse($this->_u->hasCredentials(array('test5', array('test2', 'test3'))));
		$this->assertFalse($this->_u->hasCredentials(array('test1', array('test5', 'test6'))));
	}
	
	public function teststrictCredentialComparison()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential("0");
		$this->assertTrue($this->_u->hasCredentials("0"));
		$this->assertFalse($this->_u->hasCredentials(0));
		$this->assertFalse($this->_u->hasCredentials(false));
	}

}
?>
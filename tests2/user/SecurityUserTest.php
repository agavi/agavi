<?php
class SampleSecurityUser extends AgaviSecurityUser
{
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		$this->context = $context;
		
		if(count($parameters)) {
			$this->setParameters($parameters);
		}
		$this->attributes = array();
	}
}

class SecurityUserTest extends AgaviTestCase
{
	private $_u = null;

	public function setUp()
	{
		$this->_u = new SampleSecurityUser();
		$context = AgaviContext::getInstance('test');
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
		$this->_u->addCredential('0');
		$this->assertTrue($this->_u->hasCredentials('0'));
		$this->assertFalse($this->_u->hasCredentials(0));
		$this->assertFalse($this->_u->hasCredentials(false));
	}

	public function testRemoveCredential()
	{
		$this->_u->clearCredentials();
		$this->_u->addCredential('test1');
		$this->_u->addCredential('test2');
		$this->_u->addCredential('test3');
		$this->assertTrue($this->_u->hasCredentials(array('test1', 'test2', 'test3')));
		$this->_u->removeCredential('test2');
		$this->assertTrue($this->_u->hasCredentials(array('test3', 'test1')));
		$this->assertFalse($this->_u->hasCredentials(array('test1', 'test2', 'test3')));
		$this->assertFalse($this->_u->hasCredentials(array('test2')));
		$this->_u->removeCredential('test1');
		$this->assertTrue($this->_u->hasCredentials(array('test3')));
		$this->assertFalse($this->_u->hasCredentials(array('test1')));
		$this->_u->removeCredential('test3');
		$this->assertFalse($this->_u->hasCredentials(array('test3')));
	}

	public function testSetIsAuthenticated()
	{
		$u = $this->_u;
		$this->assertFalse($u->isAuthenticated());
		$u->setAuthenticated(1);
		$this->assertFalse($u->isAuthenticated());
		$u->setAuthenticated(true);
		$this->assertTrue($u->isAuthenticated());
		$u->setAuthenticated(1);
		$this->assertFalse($u->isAuthenticated());
		$u->setAuthenticated(true);
		$this->assertTrue($u->isAuthenticated());
		$u->setAuthenticated(false);
		$this->assertFalse($u->isAuthenticated());
	}

}
?>
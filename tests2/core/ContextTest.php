<?php

// pseudo class used in test
class TestSessionStorage extends AgaviStorage
{
	public function & read($key)
	{
		$ret = null;
		return $ret;
	}
	public function & remove($key)
	{
		$ret = null;
		return $ret;
	}
	public function shutdown()
	{
	}
	public function write($key, &$data)
	{
	}
}

class ContextTest extends AgaviTestCase 
{
	public function setup()
	{
	}

	public function testGetDefaultContextInstance()
	{
		// see tests/sandbox/config/contexts.ini 
		$cfg = include(AgaviConfigCache::checkConfig('config/contexts.ini'));
		$default = $cfg['contexts']['default'];
		
		$this->assertNotNull(AgaviContext::getInstance());
		$this->assertType('AgaviContext', AgaviContext::getInstance());
		$a = AgaviContext::getInstance();
		$b = AgaviContext::getInstance('default');
		$c = AgaviContext::getInstance('DeFaULT');
		$d = AgaviContext::getInstance($default);
		$this->assertReference($a, $b);
		$this->assertReference($a, $c);
		$this->assertReference($b, $c);
		$this->assertReference($a, $d);
		
		$e = AgaviContext::getInstance('test1'); // different animal
		$this->assertCopy($a, $e);
		$f = AgaviContext::getInstance(); // we should be getting the default (test) not the last (test1)
		$this->assertReference($a, $f);
		$this->assertCopy($e, $f);
		
		$this->assertType($cfg[$default]['action_stack'], AgaviContext::getInstance()->getController()->getActionStack());
		$this->assertType($cfg[$default]['request'], AgaviContext::getInstance()->getRequest());
		$this->assertType($cfg[$default]['database_manager'], AgaviContext::getInstance()->getDatabaseManager());
	}

	public function testGetAlternateContextInstance()
	{
		$this->assertNotNull(AgaviContext::getInstance());
		$this->assertType('AgaviContext', AgaviContext::getInstance());
		$this->assertNotNull(AgaviContext::getInstance('test1'));
		$this->assertType('AgaviContext', AgaviContext::getInstance('test1'));
		$a = AgaviContext::getInstance('test1');
		$b = AgaviContext::getInstance();
		$this->assertCopy($a, $b);
		
	}

	public function testCanReinitializeContextWithOverrides()
	{
		$context = AgaviContext::getInstance();
		$context->initialize('default', array('storage' => 'TestSessionStorage'));
		$this->assertType('TestSessionStorage', $context->getStorage());
/*
		Mock::generate('ActionStack');	
		$context->initialize('default', array('action_stack' => 'MockActionStack'));
		$this->assertType('MockActionStack', $context->getController()->getActionStack());
*/
		$this->assertTrue(false, 'testCanReinitializeContextWithOverrides partially disabled for now because MockActionStack and MockActionEntry are not working');
	}


}


?>
<?php
require_once dirname(__FILE__) . '/../test_environment.php';

// pseudo class used in test
class TestSessionStorage extends Storage
{
	public function & read($key)
	{
	}
	public function & remove($key)
	{
	}
	public function shutdown()
	{
	}
	public function write($key, &$data)
	{
	}
}

class ContextTest extends UnitTestCase 
{
	public function setup()
	{
	}

	public function testGetDefaultContextInstance()
	{
		// see tests/sandbox/config/contexts.ini 
		$cfg = include(ConfigCache::checkConfig('config/contexts.ini'));
		$default = $cfg['contexts']['default'];
		
		$this->assertNotNull(Context::getInstance());
		$this->assertIsA(Context::getInstance(), 'Context');
		$a = Context::getInstance();
		$b = Context::getInstance('default');
		$c = Context::getInstance('DeFaULT');
		$d = Context::getInstance($default);
		$this->assertReference($a, $b);
		$this->assertReference($a, $c);
		$this->assertReference($b, $c);
		$this->assertReference($a, $d);
		
		$e = Context::getInstance('test1'); // different animal
		$this->assertCopy($a, $e);
		$f = Context::getInstance(); // we should be getting the default (test) not the last (test1)
		$this->assertReference($a, $f);
		$this->assertCopy($e, $f);
		
		$this->assertIsA(Context::getInstance()->getActionStack(), $cfg[$default]['action_stack']);
		$this->assertIsA(Context::getInstance()->getRequest(), $cfg[$default]['request']);
		$this->assertIsA(Context::getInstance()->getDatabaseManager(), $cfg[$default]['database_manager']);
	}

	public function testGetAlternateContextInstance()
	{
		$this->assertNotNull(Context::getInstance());
		$this->assertIsA(Context::getInstance(), 'Context');
		$this->assertNotNull(Context::getInstance('test1'));
		$this->assertIsA(Context::getInstance('test1'), 'Context');
		$a = Context::getInstance('test1');
		$b = Context::getInstance();
		$this->assertCopy($a, $b);
		
	}

	public function testCanReinitializeContextWithOverides()
	{
		$context = Context::getInstance();
		$context->initialize('default', array('storage' => 'TestSessionStorage'));
		$this->assertIsA($context->getStorage(), 'TestSessionStorage');
		Mock::generate('ActionStack');	
		$context->initialize('default', array('action_stack' => 'MockActionStack'));
		$this->assertIsA($context->getActionStack(), 'MockActionStack');
	}


}


?>
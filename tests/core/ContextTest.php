<?php
require_once dirname(__FILE__) . '/../test_environment.php';
class MockSessionStorage extends Storage
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
	public function testGetDefaultContextInstance()
	{
		$this->assertNotNull(Context::getInstance());
		$this->assertIsA(Context::getInstance(), 'Context');
		$a = Context::getInstance();
		$b = Context::getInstance('default');
		$c = Context::getInstance('DeFaULT');
		$this->assertReference($a, $b);
		$this->assertReference($a, $c);
		$this->assertReference($b, $c);
		
		$cfg = include(ConfigCache::checkConfig('config/contexts.ini'));
		$this->assertIsA(Context::getInstance()->getActionStack(), $cfg['default']['action_stack']);
		$this->assertIsA(Context::getInstance()->getRequest(), $cfg['default']['request']);
	}

	public function testGetAlternateContextInstance()
	{
		$this->assertNotNull(Context::getInstance());
		$this->assertIsA(Context::getInstance(), 'Context');
		$this->assertNotNull(Context::getInstance('Console'));
		$this->assertIsA(Context::getInstance('Console'), 'Context');
		$a = Context::getInstance('Console');
		$b = Context::getInstance();
		$this->assertCopy($a, $b);
		
	}

	public function testCanReinitializeContextWithOverides()
	{
		$context = Context::getInstance();
		Mock::generate('SessionStorage');
		$context->initialize('default', array('storage' => 'MockSessionStorage'));
		$this->assertIsA($context->getStorage(), 'MockSessionStorage');
	}


}


?>

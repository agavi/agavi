<?php
require_once dirname(__FILE__) . '/../test_environment.php';
//require_once dirname(__FILE__) . '/../mockContext.php';

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


}


?>

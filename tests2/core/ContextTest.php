<?php

// pseudo class used in test
class TestSessionStorage extends AgaviStorage
{
	public function read($key)
	{
		return null;
	}
	public function remove($key)
	{
		return null;
	}
	public function shutdown()
	{
	}
	public function write($key, $data)
	{
	}
}

class ContextTest extends AgaviTestCase 
{
	public function setup()
	{
		AgaviContext::getInstance()->initialize();
	}

	public function testGetDefaultContextInstance()
	{
		$default = AgaviConfig::get('core.default_context');
		
		$this->assertNotNull(AgaviContext::getInstance());
		$this->assertInstanceOf('AgaviContext', AgaviContext::getInstance());
		$a = AgaviContext::getInstance();
		$b = AgaviContext::getInstance(AgaviConfig::get('core.default_context'));
		$c = AgaviContext::getInstance(AgaviConfig::get('core.default_context'));
		$d = AgaviContext::getInstance($default);
		$this->assertReference($a, $b);
		$this->assertReference($a, $c);
		$this->assertReference($b, $c);
		$this->assertReference($a, $d);
		
		$e = AgaviContext::getInstance('test1'); // different animal
		$this->assertNotSame($a, $e);
		$f = AgaviContext::getInstance(); // we should be getting the default (test) not the last (test1)
		$this->assertReference($a, $f);
		$this->assertNotSame($e, $f);
		
		$this->assertInstanceOf('AgaviWebRequest', AgaviContext::getInstance('test')->getRequest());
		$this->assertInstanceOf('TestRouting', AgaviContext::getInstance('test')->getRouting());
	}

	public function testGetAlternateContextInstance()
	{
		$this->assertNotNull(AgaviContext::getInstance('test'));
		$this->assertInstanceOf('AgaviContext', AgaviContext::getInstance('test'));
		$this->assertNotNull(AgaviContext::getInstance('test1'));
		$this->assertInstanceOf('AgaviContext', AgaviContext::getInstance('test1'));
		$a = AgaviContext::getInstance('test1');
		$b = AgaviContext::getInstance('test');
		$this->assertNotSame($a, $b);
		
	}

	// public function testCanReinitializeContextWithOverrides()
	// {
	// 	$context = AgaviContext::getInstance('test');
	// 	$context->initialize('test1');
	// 	$this->assertInstanceOf('TestSessionStorage', $context->getStorage());
	// }


	public function testGetGlobalModel()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('SampleModel', $ctx->getModel('Sample'));
		$this->assertInstanceOf('SingletonSampleModel', $ctx->getModel('SingletonSample'));
		$firstSingleton = $ctx->getModel('SingletonSample');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $ctx->getModel('SingletonSample');
		$this->assertReference($firstSingleton, $secondSingleton);
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testGetModel()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('Test_TestModel', $ctx->getModel('Test', 'Test'));
		$this->assertInstanceOf('Test_SingletonTestModel', $ctx->getModel('SingletonTest', 'Test'));
		$firstSingleton = $ctx->getModel('SingletonTest', 'Test');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $ctx->getModel('SingletonTest', 'Test');
		$this->assertReference($firstSingleton, $secondSingleton);
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}
	
	public function testGetDotStyleModel()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('Foo_Bar_BazModel', $ctx->getModel('Foo.Bar.Baz'));
		$this->assertInstanceOf('Test_Foo_Bar_BazModel', $ctx->getModel('Foo.Bar.Baz', 'Test'));
	}

	public function testGetFactoryInfo()
	{
		$ctx = AgaviContext::getInstance('test');
		$info_ex = array('class' => 'TestResponse', 'parameters' => array());
		$this->assertSame($info_ex, $ctx->getFactoryInfo('response'));
	}

	public function testGetController()
	{
		$ctx = AgaviContext::getInstance('test');
		$c = new PHPUnit_Framework_Constraint_IsInstanceOf('AgaviController');
		$this->assertThat($ctx->getController(), $c);
	}

	public function testGetDatabaseManager()
	{
		$this->assertNull(AgaviContext::getInstance('test')->getDatabaseManager());

		// clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', AgaviConfig::get('core.default_context')));
		AgaviConfig::set('core.use_database', true);
		AgaviContext::getInstance('test')->initialize();
		$this->assertInstanceOf('AgaviDatabaseManager', AgaviContext::getInstance('test')->getDatabaseManager());
		AgaviConfig::set('core.use_database', false);
	}

	public function testGetLoggerManager()
	{
		$this->assertInstanceOf('AgaviLoggerManager', AgaviContext::getInstance('test')->getLoggerManager());

		// this BS just won't work... zomg tests suck suck suck suck suck
		
		// // clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		// unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', AgaviConfig::get('core.default_context')));
		// AgaviConfig::set('core.use_logging', false);
		// AgaviContext::getInstance('test')->initialize();
		// $this->assertNull(AgaviContext::getInstance('test')->getLoggerManager());
		// unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', AgaviConfig::get('core.default_context')));
		// AgaviConfig::set('core.use_logging', true);
		// AgaviContext::getInstance('test')->initialize();
	}

	public function testGetName()
	{
		$this->assertSame(AgaviConfig::get('core.default_context'), AgaviContext::getInstance('test')->getName());
		$this->assertSame('test1', AgaviContext::getInstance('test1')->getName());
	}

	public function testGetRequest()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('AgaviRequest', $ctx->getRequest());
	}

	public function testGetRouting()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('AgaviRouting', $ctx->getRouting());
	}

	public function testGetStorage()
	{
		$ctx = AgaviContext::getInstance('test');
		$this->assertInstanceOf('AgaviStorage', $ctx->getStorage());
	}

	public function testGetUser()
	{
		$this->assertInstanceOf('AgaviUser', AgaviContext::getInstance('test')->getUser());

		// clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', AgaviConfig::get('core.default_context')));
		AgaviConfig::set('core.use_security', true);
		AgaviContext::getInstance('test')->initialize();
		$this->assertInstanceOf('AgaviSecurityUser', AgaviContext::getInstance('test')->getUser());
	}
}


?>
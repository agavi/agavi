<?php

class AgaviContextTest extends AgaviPhpUnitTestCase
{
	public function testGetInstance()
	{
		$instance = AgaviContext::getInstance('foo');
		$this->assertNotNull($instance);
		$this->assertInstanceOf('AgaviContext', $instance);
	}
	
	public function testSameInstanceForSameProfile()
	{
		$instance1 = AgaviContext::getInstance('foo');
		$instance2 = AgaviContext::getInstance('foo');
		$this->assertSame($instance1, $instance2);
	}
	
	public function testDifferentInstanceForDifferentProfile()
	{
		$instance1 = AgaviContext::getInstance('foo');
		$instance2 = AgaviContext::getInstance('bar');
		$this->assertNotSame($instance1, $instance2);
	}
	
	public function testGetName()
	{
		$this->assertSame(AgaviConfig::get('core.default_context'), AgaviContext::getInstance()->getName());
		$this->assertSame('test1', AgaviContext::getInstance('test1')->getName());
	}
	
	/**
	 * @dataProvider dataGetModel
	 */
	public function testGetModel($modelName, $className, $isSingleton, $module = null)
	{
		$ctx = AgaviContext::getInstance();
		$model1 = $ctx->getModel($modelName, $module);
		$model2 = $ctx->getModel($modelName, $module);
		$this->assertInstanceOf($className, $model1);
		$this->assertInstanceOf($className, $model2);
		if($isSingleton) {
			$this->assertSame($model1, $model2);
		} else {
			$this->assertNotSame($model1, $model2);
		}
	}
	
	public function dataGetModel() {
		return array(
			'global normal model' => array('ContextTest', 'ContextTestModel', false),
			'global singleton model' => array('ContextTestSingleton', 'ContextTestSingletonModel', true),
			'global model in child path' => array('ContextTest.Child.Test', 'ContextTest_Child_TestModel', false),
			'module normal model' => array('Test', 'ContextTest_TestModel', false, 'ContextTest'),
			'module singleton model' => array('TestSingleton', 'ContextTest_TestSingletonModel', true, 'ContextTest'),
			'module model in child path' => array('Parent.Child.Test', 'ContextTest_Parent_Child_TestModel', false, 'ContextTest'),
		);
	}
	


	public function testGetFactoryInfo()
	{
		$ctx = AgaviContext::getInstance('test');
		$expected = array('class' => 'AgaviWebResponse', 'parameters' => array());
		$this->assertSame($expected, $ctx->getFactoryInfo('response'));
	}

	public function testGetController()
	{
		$this->assertInstanceOf('AgaviController', AgaviContext::getInstance()->getController());
	}

	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_database_off
	 */
	public function testGetDatabaseManagerOff()
	{
		$this->assertNull(AgaviContext::getInstance()->getDatabaseManager());
	}

	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_database_on
	 */
	public function testGetDatabaseManagerOn()
	{
		$this->assertInstanceOf('AgaviDatabaseManager', AgaviContext::getInstance()->getDatabaseManager());
	}
	
	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_security_off
	 */
	public function testGetUserSecurityOff()
	{
		$this->assertInstanceOf('AgaviUser', AgaviContext::getInstance()->getUser());
		$this->assertNotInstanceOf('AgaviSecurityUser', AgaviContext::getInstance()->getUser());
	}

	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_security_on
	 */
	public function testGetUserSecurityOn()
	{
		$this->assertInstanceOf('AgaviSecurityUser', AgaviContext::getInstance()->getUser());
	}

	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_translation_off
	 */
	public function testGetTranslationManagerOff()
	{
		$this->assertNull(AgaviContext::getInstance()->getTranslationManager());
	}

	/**
	 * @runInSeparateProcess
	 * @agaviIsolationEnvironment testing-use_logging_on
	 */
	public function testGetTranslationManagerOn()
	{
		$this->assertInstanceOf('AgaviTranslationManager', AgaviContext::getInstance()->getTranslationManager());
	}

	public function testGetLoggerManager()
	{
		$this->assertInstanceOf('AgaviLoggerManager', AgaviContext::getInstance()->getLoggerManager());
	}

	public function testGetRequest()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertInstanceOf('AgaviRequest', $ctx->getRequest());
	}

	public function testGetRouting()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertInstanceOf('AgaviRouting', $ctx->getRouting());
	}

	public function testGetStorage()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertInstanceOf('AgaviStorage', $ctx->getStorage());
	}
}

?>
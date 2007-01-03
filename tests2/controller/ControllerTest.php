<?php

class TestController extends AgaviController
{
	public function redirect($to)
	{
		throw new AgaviException('N/A');
	}
}

class ControllerTest extends AgaviTestCase
{
	protected $_controller = null;

	public function setUp()
	{
		// ReInitialize the Context between tests to start fresh
		$this->_context = AgaviContext::getInstance('test');
		$this->_context->initialize();
		//$this->_controller = new TestController();
		$response = new AgaviWebResponse();
		$response->initialize($this->_context);
		$this->_controller = AgaviContext::getInstance('test')->getController();
		$this->_controller->initialize($response, array());


	}

	public function testNewController()
	{
		$controller = $this->_controller;
		$this->assertType('AgaviWebController', $controller);
		$this->assertType('AgaviContext', $controller->getContext());
		$ctx1 = $controller->getContext();
		$ctx2 = AgaviContext::getInstance('test');
		$this->assertReference($ctx1, $ctx2);
	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem, 
		$this->assertTrue(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Test/actions/TestAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Test/actions/BunkAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Bunk/actions/BunkAction.class.php'));
		$controller = $this->_controller;
		$this->assertEquals('Test', $controller->resolveAction('Test', 'Test'));
		$this->assertFalse($controller->resolveAction('Test', 'Bunk'));
		$this->assertFalse($controller->resolveAction('Bunk', 'Bunk'));
	}

	public function testGetActionFromModule()
	{
		// TODO: check all other existing naming schemes for actions

		$action = $this->_controller->getAction('Test', 'Test');
		$this->assertType('Test_TestAction', $action);
		$this->assertType('AgaviAction', $action);

		// TODO: this needs checking for errors 
//		$this->_controller->getAction('Test', 'NonExistant');
	}

	public function testGetContext()
	{
		$ctx1 = AgaviContext::getInstance('test');
		$ctx2 = AgaviContext::getInstance('test')->getController()->getContext();
		$this->assertType('AgaviContext', $ctx1);
		$this->assertType('AgaviContext', $ctx2);
		$this->assertReference($ctx1, $ctx2);
	}

	public function testgetInstance()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$this->assertType('AgaviController', $controller);
	}

	public function testGetView()
	{
		$controller = $this->_controller;
		$this->assertType('Test_TestSuccessView', $controller->getView('Test', 'TestSuccess'));
		$this->assertType('Test_TestErrorView', $controller->getView('Test', 'TestError'));
	}

	public function testModelExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->modelExists('Test', 'Test'));
		$this->assertFalse($controller->modelExists('Test', 'Bunk'));
		$this->assertFalse($controller->modelExists('Bunk', 'Bunk'));
	}

	public function testModuleExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->moduleExists('Test'));
		$this->assertFalse($controller->moduleExists('Bunk'));
	}

	public function testViewExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($controller->viewExists('Bunk', 'Bunk'));
	}



	public function testSetGetOutputType()
	{
		$controller = $this->_controller;
		$this->assertSame('html', $controller->getOutputType());

		$this->assertTrue($controller->setOutputType('test1'));
		$this->assertSame('test1', $controller->getOutputType());

		$this->assertTrue($controller->setOutputType('test2'));
		$this->assertSame('test2', $controller->getOutputType());

		try {
			$controller->setOutputType('nonexistant');
			$this->fail('Expected AgaviException not thrown!');
		} catch(AgaviException $e) {
			$this->assertSame('test2', $controller->getOutputType());
		}
/*
		$controller->getResponse()->lock();
		$this->assertFalse($controller->setOutputType('html'));
		$this->assertSame('test2', $controller->getOutputType());
*/
	}

	public function testGetOutputTypeInfo()
	{
		$controller = $this->_controller;

		$info_ex = array(
			'parameters' =>							array('Content-Type' => 'text/html'),
			'renderer_parameters' =>		array(),
			'renderer' =>								'AgaviPhpRenderer',
		);

		$info = $controller->getOutputTypeInfo();
		$this->assertSame($info_ex, $info);

		$info_ex = array(
			'parameters' =>							array(),
			'renderer_parameters' =>		array(),
			'renderer' =>								'AgaviPhpRenderer',
		);
		$info = $controller->getOutputTypeInfo('test1');
		$this->assertSame($info_ex, $info);

		try {
			$controller->getOutputTypeInfo('nonexistant');
			$this->fail('Expected AgaviException not thrown!');
		} catch(AgaviException $e) {
		}
	}


/* 
	// TODO: moved to AgaviResponse
	public function testsetContentType()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$ctype = $controller->getContentType();
		$controller->setContentType('image/jpeg');
		$this->assertEquals($controller->getContentType(), 'image/jpeg');
		$controller->setContentType($ctype);
	}
	
	public function testclearHTTPHeaders()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$controller->clearHTTPHeaders();
		$this->assertEquals($controller->getHTTPHeaders(), array());
	}
	
	public function testgetHTTPHeader()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$this->assertEquals($controller->getHTTPHeader('unset'), null);
	}

	public function testhasHTTPHeader()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$controller->clearHTTPHeaders();
		$controller->setHTTPHeader('testme', 'whatever');
		$this->assertTrue($controller->hasHTTPHeader('testme'));
		$this->assertFalse($controller->hasHTTPHeader('iamnotset'));
	}
	
	public function testsetHTTPHeader()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$controller->setHTTPHeader('sometest', 'fubar');
		$this->assertEquals($controller->getHTTPHeader('sometest'), array('fubar'));
		$controller->setHTTPHeader('sometest', 'foo');
		$this->assertEquals($controller->getHTTPHeader('sometest'), array('foo'));
		$controller->setHTTPHeader('sometest', 'bar', false);
		$this->assertEquals($controller->getHTTPHeader('sometest'), array('foo', 'bar'));
		$controller->setHTTPHeader('multiple', array('first', 'second'));
		$this->assertEquals($controller->getHTTPHeader('multiple'), array('first', 'second'));
	}
	
	public function testgetHTTPStatusCode()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$this->assertEquals($controller->getHTTPStatusCode(), null);
	}
	
	public function testsetHTTPStatusCode()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$controller->setHTTPStatusCode('404');
		$this->assertEquals($controller->getHTTPStatusCode(), '404');
		$controller->setHTTPStatusCode(403);
		$this->assertEquals($controller->getHTTPStatusCode(), '403');
		$controller->setHTTPStatusCode('123');
		$this->assertEquals($controller->getHTTPStatusCode(), '403');
		$controller->setHTTPStatusCode(123);
		$this->assertEquals($controller->getHTTPStatusCode(), '403');
	}
	
	// TODO: moved to routing
	function testgenURL()
	{
		$routing = AgaviContext::getInstance('test')->getRouting();
		$this->assertEquals($controller->genURL('index.php', array('foo' =>'bar')), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(null, array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar'), 'index.php'), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
	}
*/
}

?>
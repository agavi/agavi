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
		$this->_controller = AgaviContext::getInstance('test')->getController();
		$this->_controller->initialize($this->_context, array());


	}

	public function testNewController()
	{
		$controller = $this->_controller;
		$c = new PHPUnit_Framework_Constraint_IsInstanceOf('AgaviController');
		$this->assertThat($controller, $c);
		$c = new PHPUnit_Framework_Constraint_IsInstanceOf('AgaviContext');
		$this->assertThat($controller->getContext(), $c);
		$ctx1 = $controller->getContext();
		$ctx2 = AgaviContext::getInstance('test');
		$this->assertReference($ctx1, $ctx2);
	}

	public function testactionFileExists()
	{
		// actionExists actually checks the filesystem, 
		$this->assertTrue(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Test/actions/TestAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Test/actions/BunkAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Bunk/actions/BunkAction.class.php'));
		$controller = $this->_controller;
		$this->assertEquals(AgaviConfig::get('core.app_dir') . '/modules/Test/actions/TestAction.class.php', $controller->checkActionFile('Test', 'Test'));
		$this->assertFalse($controller->checkActionFile('Test', 'Bunk'), 'actionFileExists did not return false for non-existing action in existing module');
		$this->assertFalse($controller->checkActionFile('Bunk', 'Bunk'), 'actionFileExists did not return false for non-existing action in non-existing module');
	}

	public function testGetActionFromModule()
	{
		// TODO: check all other existing naming schemes for actions

		$action = $this->_controller->createActionInstance('Test', 'Test');
		$this->assertInstanceOf('Test_TestAction', $action);
		$this->assertInstanceOf('AgaviAction', $action);

		// TODO: this needs checking for errors 
//		$this->_controller->createActionInstance('Test', 'NonExistant');
	}

	public function testGetContext()
	{
		$ctx1 = AgaviContext::getInstance('test');
		$ctx2 = AgaviContext::getInstance('test')->getController()->getContext();
		$this->assertInstanceOf('AgaviContext', $ctx1);
		$this->assertInstanceOf('AgaviContext', $ctx2);
		$this->assertReference($ctx1, $ctx2);
	}

	public function testgetInstance()
	{
		$controller = AgaviContext::getInstance('test')->getController();
		$this->assertInstanceOf('AgaviController', $controller);
	}

	public function testCreateViewInstance()
	{
		$controller = $this->_controller;
		$this->assertInstanceOf('Test_TestSuccessView', $controller->createViewInstance('Test', 'TestSuccess'));
		$this->assertInstanceOf('Test_TestErrorView', $controller->createViewInstance('Test', 'TestError'));
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



	public function testGetOutputTypeInfo()
	{
		$controller = $this->_controller;

		$info_ex = array(
			'http_headers' => array(
				'Content-Type' => 'text/html',
			),
		);

		$info = $controller->getOutputType();
		$this->assertSame($info_ex, $info->getParameters());

		$info_ex = array(
		);
		$info = $controller->getOutputType('test1');
		$this->assertSame($info_ex, $info->getParameters());

		try {
			$controller->getOutputType('nonexistant');
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
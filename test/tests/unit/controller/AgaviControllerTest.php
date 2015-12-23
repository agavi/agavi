<?php

class TestController extends AgaviController
{
	public function redirect($to)
	{
		throw new AgaviException('N/A');
	}
}

/**
 * runTestsInSeparateProcesses
 */
class AgaviControllerTest extends AgaviUnitTestCase
{
	protected $_controller = null;

	public function setUp()
	{
		// ReInitialize the Context between tests to start fresh
		$this->_context = $this->getContext();
		$this->_controller = $this->_context->getController();
		$this->_controller->initialize($this->_context, array());
	}

	public function testNewController()
	{
		$controller = $this->_controller;
		$this->assertInstanceOf('AgaviController', $controller);
		$this->assertInstanceOf('AgaviContext', $controller->getContext());
		$ctx1 = $controller->getContext();
		$ctx2 = $this->getContext();
		$this->assertSame($ctx1, $ctx2);
	}

	public function testactionFileExists()
	{
		// actionExists actually checks the filesystem, 
		$this->assertTrue(file_exists(AgaviConfig::get('core.app_dir') . '/modules/ControllerTests/actions/ControllerTestAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/ControllerTests/actions/BunkAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.app_dir') . '/modules/Bunk/actions/BunkAction.class.php'));
		$controller = $this->_controller;
		$this->assertEquals(AgaviConfig::get('core.app_dir') . '/modules/ControllerTests/actions/ControllerTestAction.class.php', $controller->checkActionFile('ControllerTests', 'ControllerTest'));
		$this->assertFalse($controller->checkActionFile('ControllerTests', 'Bunk'), 'actionFileExists did not return false for non-existing action in existing module');
		$this->assertFalse($controller->checkActionFile('Bunk', 'Bunk'), 'actionFileExists did not return false for non-existing action in non-existing module');
	}

	public function testGetActionFromModule()
	{
		// TODO: check all other existing naming schemes for actions

		$action = $this->_controller->createActionInstance('ControllerTests', 'ControllerTest');
		$this->assertInstanceOf('ControllerTests_ControllerTestAction', $action);
		$this->assertInstanceOf('AgaviAction', $action);

	}

	/**
	 * @expectedException AgaviFileNotFoundException
	 */
	public function testGetInvalidActionFromModule() {
		$this->_controller->createActionInstance('ControllerTests', 'NonExistant');
	}

	public function testGetContext()
	{
		$this->assertSame($this->getContext(), $this->getContext()->getController()->getContext());
	}

	public function testCreateViewInstance()
	{
		$controller = $this->_controller;
		$this->assertInstanceOf(
			'ControllerTests_ControllerTestSuccessView',
			$controller->createViewInstance('ControllerTests', 'ControllerTestSuccess')
		);
		$this->assertInstanceOf(
			'ControllerTests_ControllerTestErrorView',
			$controller->createViewInstance('ControllerTests', 'ControllerTestError')
		);
	}

	public function testModelExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->modelExists('ControllerTests', 'ControllerTest'));
		$this->assertFalse($controller->modelExists('Test', 'Bunk'));
		$this->assertFalse($controller->modelExists('Bunk', 'Bunk'));
	}

	public function testModuleExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->moduleExists('ControllerTests'));
		$this->assertFalse($controller->moduleExists('Bunk'));
	}

	public function testViewExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->viewExists('ControllerTests', 'ControllerTestSuccess'));
		$this->assertFalse($controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($controller->viewExists('Bunk', 'Bunk'));
	}



	public function testGetOutputTypeInfo()
	{
		$controller = $this->_controller;

		$info_ex = array(
			'http_headers' => array(
				'Content-Type' => 'text/html; charset=UTF-8',
			),
		);

		$info = $controller->getOutputType();
		$this->assertSame($info_ex, $info->getParameters());

		$info_ex = array(
		);
		$info = $controller->getOutputType('controllerTest');
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
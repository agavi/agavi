<?php

class TestController extends AgaviController
{
}

class ControllerTest extends AgaviTestCase
{
	protected $_controller = null;

	public function setUp()
	{
		// ReInitialize the Context between tests to start fresh
		$this->_context = AgaviContext::getInstance();
		$this->_context->initialize();
		//$this->_controller = new TestController();
		$this->_controller = AgaviContext::getInstance()->getController();
		$this->_controller->initialize(AgaviContext::getInstance(), array());


	}

	public function testNewController()
	{
		$controller = $this->_controller;
		$this->assertType('AgaviWebController', $controller);
		$this->assertType('AgaviContext', $controller->getContext());
		$ctx1 = $controller->getContext();
		$ctx2 = AgaviContext::getInstance();
		$this->assertReference($ctx1, $ctx2);
	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem, 
		$this->assertTrue(file_exists(AgaviConfig::get('core.webapp_dir') . '/modules/Test/actions/TestAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.webapp_dir') . '/modules/Test/actions/BunkAction.class.php'));
		$this->assertFalse(file_exists(AgaviConfig::get('core.webapp_dir') . '/modules/Bunk/actions/BunkAction.class.php'));
		$controller = $this->_controller;
		$this->assertTrue($controller->actionExists('Test', 'Test'));
		$this->assertFalse($controller->actionExists('Test', 'Bunk'));
		$this->assertFalse($controller->actionExists('Bunk', 'Bunk'));
	}

	public function testforwardTooTheMaxThrowsException()
	{
		AgaviConfig::set('controller.max_forwards', 20, false);
		$controller = $this->_controller;
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		for ($i=0; $i<= AgaviConfig::get('controller.max_forwards'); $i++) {
			try {
				$controller->forward('Test', 'Test');
				if ($i >= AgaviConfig::get('controller.max_forwards')) {
					$this->assertTrue(0,'Expected ForwardException not thrown');
				}
			} catch (AgaviForwardException $fe) {
				$this->assertRegexp('/too many forwards/i', $fe->getMessage());
			}
		}
	}
	
	public function testCantForwardToUnconfiguredModule()
	{
		$controller = $this->_controller;
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$controller->forward('NoConfigModule', 'Some');
			$this->assertTrue(0,'Expected ParseException not thrown, there is only an empty module.ini there!');
		} catch (AgaviParseException $e) {
			$this->assertRegexp('/Start tag expected/i', $e->getMessage());
		}
	}

	public function testForwardingToDisabledModule()
	{
		AgaviConfig::set('actions.module_disabled_module', 'ErrorModule', false);
		AgaviConfig::set('actions.module_disabled_action', 'DisabledModule', false);
		$controller = $this->_controller;
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$mode = $controller->getRenderMode();
			$this->assertEquals(AgaviView::RENDER_VAR, $mode);

			$controller->forward('UnavailableModule', 'Index');
			$lastActionEntry = $controller->getActionStack()->getLastEntry();
			$this->assertType('AgaviActionStackEntry', $lastActionEntry);
			$view = $lastActionEntry->getPresentation();
			$this->assertRegexp('/module has been disabled/i', $view->getContent());

			$module = $lastActionEntry->getModuleName();
			$action = $lastActionEntry->getActionName();
			$this->assertEquals(AgaviConfig::get('actions.module_disabled_module'), $module);
			$this->assertEquals(AgaviConfig::get('actions.module_disabled_action'), $action);
		} catch (AgaviForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
	}

	public function testForwardingSuccessfully()
	{
		$controller = $this->_controller;
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$controller->forward('Test', 'Test');
			$lastActionEntry = $controller->getActionStack()->getLastEntry();
			$this->assertType('AgaviActionStackEntry', $lastActionEntry);
			$view = $lastActionEntry->getPresentation();
			$this->assertRegexp('/test successful/i',$view->getContent());
			$module = $lastActionEntry->getModuleName();
			$action = $lastActionEntry->getActionName();
			$this->assertEquals('Test', $module);
			$this->assertEquals('Test', $action);
		} catch (AgaviForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
		
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

	public function testGetActionStack()
	{
		$con_as = $this->_controller->getActionStack();
		$this->assertType('AgaviActionStack', $con_as);
	}

	public function testGetContext()
	{
		$ctx1 = AgaviContext::getInstance();
		$ctx2 = AgaviContext::getInstance()->getController()->getContext();
		$this->assertType('AgaviContext', $ctx1);
		$this->assertType('AgaviContext', $ctx2);
		$this->assertReference($ctx1, $ctx2);
	}

	public function testGetInstance()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('AgaviController', $controller);
	}

	public function testSetGetRenderMode()
	{
		$controller = $this->_controller;
		$this->assertEquals(AgaviView::RENDER_CLIENT, $controller->getRenderMode());
		
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		$this->assertEquals(AgaviView::RENDER_VAR, $controller->getRenderMode());
		
		$controller->setRenderMode(AgaviView::RENDER_NONE);
		$this->assertEquals(AgaviView::RENDER_NONE, $controller->getRenderMode());
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

	public function testSetRenderMode()
	{
		$controller = $this->_controller;
		$good = array(AgaviView::RENDER_CLIENT, AgaviView::RENDER_VAR, AgaviVIEW::RENDER_NONE);
		$bad = array(932940, null, '');
		foreach($good as $value) {
			try {
				$controller->setRenderMode($value);
			} catch(AgaviRenderException $e) {
				$this->fail('Caught unexpected RenderException!');
			}
		}
		foreach($bad as $value) {
			try {
				$controller->setRenderMode($value);
				$this->fail('Expected RenderException not thrown!');
			} catch(AgaviRenderException $e) {
			}
		}
	}

	public function testViewExists()
	{
		$controller = $this->_controller;
		$this->assertTrue($controller->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($controller->viewExists('Bunk', 'Bunk'));
	}



	public function testGetActionName()
	{
		$controller = $this->_controller;

		$controller->forward('Test', 'Test');
		$this->assertSame($controller->getActionStack()->getLastEntry()->getActionName(), $controller->getActionName());
	}

	public function testGetModuleName()
	{
		$controller = $this->_controller;

		$controller->forward('ErrorModule', 'Some');
		$this->assertSame($controller->getActionStack()->getLastEntry()->getModuleName(), $controller->getModuleName());
	}


	public function testGetModuleDirectory()
	{
		$controller = $this->_controller;

		$controller->forward('ErrorModule', 'Some');
		$this->assertSame(AgaviConfig::get('core.webapp_dir') . '/modules/ErrorModule', $controller->getModuleDirectory());
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
			'renderer' =>								'AgaviPhpRenderer',
			'parameters' =>							array('Content-Type' => 'text/html'),
			'renderer_parameters' =>		array(),
		);

		$info = $controller->getOutputTypeInfo();
		$this->assertSame($info_ex, $info);

		$info_ex = array(
			'renderer' =>								'AgaviPhpRenderer',
			'fallback' =>								'html',
			'parameters' =>							array(),
			'renderer_parameters' =>		array(),
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
		$controller = AgaviContext::getInstance()->getController();
		$ctype = $controller->getContentType();
		$controller->setContentType('image/jpeg');
		$this->assertEquals($controller->getContentType(), 'image/jpeg');
		$controller->setContentType($ctype);
	}
	
	public function testclearHTTPHeaders()
	{
		$controller = AgaviContext::getInstance()->getController();
		$controller->clearHTTPHeaders();
		$this->assertEquals($controller->getHTTPHeaders(), array());
	}
	
	public function testgetHTTPHeader()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertEquals($controller->getHTTPHeader('unset'), null);
	}

	public function testhasHTTPHeader()
	{
		$controller = AgaviContext::getInstance()->getController();
		$controller->clearHTTPHeaders();
		$controller->setHTTPHeader('testme', 'whatever');
		$this->assertTrue($controller->hasHTTPHeader('testme'));
		$this->assertFalse($controller->hasHTTPHeader('iamnotset'));
	}
	
	public function testsetHTTPHeader()
	{
		$controller = AgaviContext::getInstance()->getController();
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
		$controller = AgaviContext::getInstance()->getController();
		$this->assertEquals($controller->getHTTPStatusCode(), null);
	}
	
	public function testsetHTTPStatusCode()
	{
		$controller = AgaviContext::getInstance()->getController();
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
		$routing = AgaviContext::getInstance()->getRouting();
		$this->assertEquals($controller->genURL('index.php', array('foo' =>'bar')), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(null, array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar'), 'index.php'), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
	}
*/
}

?>
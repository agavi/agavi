<?php

class ControllerTest extends AgaviTestCase
{

	public function setUp()
	{
		// ReInitialize the Context between tests to start fresh
		$this->_context = AgaviContext::getInstance()->initialize('default');
	}

	public function testNewController()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('AgaviFrontWebController', $controller);
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
		$controller = AgaviContext::getInstance()->getController();
		$this->assertTrue($controller->actionExists('Test', 'Test'));
		$this->assertFalse($controller->actionExists('Test', 'Bunk'));
		$this->assertFalse($controller->actionExists('Bunk', 'Bunk'));
	}

	public function testforwardTooTheMaxThrowsException()
	{
		AgaviConfig::set('controller.max_forwards', 20, false);
		$controller = AgaviContext::getInstance()->getController();
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
		$controller = AgaviContext::getInstance()->getController();
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$controller->forward('NoConfigModule', 'Some');
			$this->assertTrue(0,'Expected ParseException not thrown, there is only an empty module.ini there!');
		} catch (AgaviParseException $e) {
			$this->assertRegexp('/missing/i', $e->getMessage());
		}
	}

	public function testForwardingToDisabledModule()
	{
		AgaviConfig::set('actions.module_disabled_module', 'ErrorModule', false);
		AgaviConfig::set('actions.module_disabled_action', 'DisabledModule', false);
		$controller = AgaviContext::getInstance()->getController();
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$mode = $controller->getRenderMode();
			$this->assertEquals(AgaviView::RENDER_VAR, $mode);
			$controller->forward('UnavailableModule', 'Index');
			$lastActionEntry = $controller->getActionStack()->getLastEntry();
			$this->assertType('AgaviActionStackEntry', $lastActionEntry);
			$view = $lastActionEntry->getPresentation();
			$this->assertRegexp('/module has been disabled/i',$view);
			$module = $lastActionEntry->getModuleName();
			$action = $lastActionEntry->getActionName();
			$this->assertEquals(AgaviConfig::get('actions.module_disabled_module'), $module);
			$this->assertEquals(AgaviConfig::get('actions.module_disabled_action'), $action);
		} catch (ForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
	}

	public function testForwardingSuccessfully()
	{
		$context = AgaviContext::getInstance();
		$context->getController()->setRenderMode(AgaviView::RENDER_VAR);
		try {
			$context->getController()->forward('Test', 'Test');
			$lastActionEntry = $context->getActionStack()->getLastEntry();
			$this->assertType('AgaviActionStackEntry', $lastActionEntry);
			$view = $lastActionEntry->getPresentation();
			$this->assertRegexp('/test successful/i',$view);
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
		$action = AgaviContext::getInstance()->getController()->getAction('Test', 'Test');
		$this->assertType('Test_TestAction', $action);
		$this->assertType('AgaviAction', $action);
	}

	public function testGetActionStack()
	{
		$con_as = AgaviContext::getInstance()->getController()->getActionStack();
		$ctx_as = AgaviContext::getInstance()->getActionStack();
		$this->assertType('AgaviActionStack', $con_as);
		$this->assertType('AgaviActionStack', $ctx_as);
		$this->assertReference($ctx_as, $con_as);
	}

	public function testGetContext()
	{
		$ctx1 = AgaviContext::getInstance();
		$ctx2 = AgaviContext::getInstance()->getController()->getContext();
		$this->assertType('AgaviContext', $ctx1);
		$this->assertType('AgaviContext', $ctx2);
		$this->assertReference($ctx1, $ctx2);
	}

	public function testGetGlobalModel()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('SampleModel', $controller->getGlobalModel('Sample'));
		$this->assertType('SingletonSampleModel', $controller->getGlobalModel('SingletonSample'));
		$firstSingleton = $controller->getGlobalModel('SingletonSample');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getGlobalModel('SingletonSample');
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}
	
	public function testGetGlobalModel_recursive()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('SampleRecursiveModel', $controller->getGlobalModel('SampleRecursive'));
		$this->assertType('SingletonSampleRecursiveModel', $controller->getGlobalModel('SingletonSampleRecursive'));
		$firstSingleton = $controller->getGlobalModel('SingletonSampleRecursive');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getGlobalModel('SingletonSampleRecursive');
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testGetInstance()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('AgaviController', $controller);
	}

	public function testGetModel()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('Test_TestModel', $controller->getModel('Test', 'Test'));
		$this->assertType('Test2Model', $controller->getModel('Test', 'Test2'));
		$this->assertType('Test_SingletonTestModel', $controller->getModel('Test', 'SingletonTest'));
		$this->assertType('SingletonTest2Model', $controller->getModel('Test', 'SingletonTest2'));
		$firstSingleton = $controller->getModel('Test', 'SingletonTest');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getModel('Test', 'SingletonTest');
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testSetGetRenderMode()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertEquals(AgaviView::RENDER_CLIENT, $controller->getRenderMode());
		
		$controller->setRenderMode(AgaviView::RENDER_VAR);
		$this->assertEquals(AgaviView::RENDER_VAR, $controller->getRenderMode());
		
		$controller->setRenderMode(AgaviView::RENDER_NONE);
		$this->assertEquals(AgaviView::RENDER_NONE, $controller->getRenderMode());
	}

	public function testGetView()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertType('Test_TestSuccessView', $controller->getView('Test', 'TestSuccess'));
		$this->assertType('Test_TestErrorView', $controller->getView('Test', 'TestError'));
	}

	public function testModelExists()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertTrue($controller->modelExists('Test', 'Test'));
		$this->assertFalse($controller->modelExists('Test', 'Bunk'));
		$this->assertFalse($controller->modelExists('Bunk', 'Bunk'));
	}

	public function testModuleExists()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertTrue($controller->moduleExists('Test'));
		$this->assertFalse($controller->moduleExists('Bunk'));
	}

	public function testSetRenderMode()
	{
		$controller = AgaviContext::getInstance()->getController();
		$good = array(AgaviView::RENDER_CLIENT, AgaviView::RENDER_VAR, AgaviVIEW::RENDER_NONE);
		$bad = array(932940, null, '');
		foreach ($good as &$value) {
			try {
				$controller->setRenderMode($value);
			} catch (AgaviRenderException $e) {
				$this->fail('Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$controller->setRenderMode($value);
				$this->fail('Expected RenderException not thrown!');
			} catch (AgaviRenderException $e) {
			}
		}
	}

	public function testViewExists()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertTrue($controller->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($controller->viewExists('Bunk', 'Bunk'));
	}

	public function testinCLI()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertEquals((php_sapi_name() == 'cli'), $controller->inCLI());
	}
	
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
	
	function testgenURL()
	{
		$controller = AgaviContext::getInstance()->getController();
		$this->assertEquals($controller->genURL('index.php', array('foo' =>'bar')), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(null, array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar'), 'index.php'), 'index.php?foo=bar');
		$this->assertEquals($controller->genURL(array('foo' =>'bar')), $_SERVER['SCRIPT_NAME'] . '?foo=bar');
	}
}

?>
<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class ControllerTest extends UnitTestCase
{

	public function setUp()
	{
		// ReInitialize the Context between tests to start fresh
		$this->_context = Context::getInstance()->initialize('default');
	}

	public function testNewController()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller, 'FrontWebController');
		$this->assertIsA($controller->getContext(), 'Context');
		$ctx1 = $controller->getContext();
		$ctx2 = Context::getInstance();
		$this->assertReference($ctx1, $ctx2);
	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem, 
		$this->assertTrue(file_exists(AG_WEBAPP_DIR . '/modules/Test/actions/TestAction.class.php'));
		$this->assertFalse(file_exists(AG_WEBAPP_DIR . '/modules/Test/actions/BunkAction.class.php'));
		$this->assertFalse(file_exists(AG_WEBAPP_DIR . '/modules/Bunk/actions/BunkAction.class.php'));
		$controller = Context::getInstance()->getController();
		$this->assertTrue($controller->actionExists('Test', 'Test'));
		$this->assertFalse($controller->actionExists('Test', 'Bunk'));
		$this->assertFalse($controller->actionExists('Bunk', 'Bunk'));
	}

	public function testforwardTooTheMaxThrowsException()
	{
			
		if (!defined('AG_MAX_FORWARDS')) {
			define('AG_MAX_FORWARDS', 20);
		}
		$controller = Context::getInstance()->getController();
		$controller->setRenderMode(View::RENDER_VAR);
		for ($i=0; $i<= AG_MAX_FORWARDS; $i++) {
			try {
				$controller->forward('Test', 'Test');
				if ($i >= AG_MAX_FORWARDS) {
					$this->assertTrue(0,'Expected ForwardException not thrown');
				}
			} catch (ForwardException $fe) {
				$this->assertWantedPattern('/too many forwards/i', $fe->getMessage());
			}
		}
	}
	
	public function testCantForwardToUnconfiguredModule()
	{
		$controller = Context::getInstance()->getController();
		$controller->setRenderMode(View::RENDER_VAR);
		try {
			$controller->forward('NoConfigModule', 'Some');
			$this->assertTrue(0,'Expected ParseException not thrown, there is only an empty module.ini there!');
		} catch (ParseException $e) {
			$this->assertWantedPattern('/missing/i', $e->getMessage());
		}
	}

	public function testForwardingToDisabledModule()
	{
		if (!defined('AG_MODULE_DISABLED_MODULE')) {
			define('AG_MODULE_DISABLED_MODULE', 'ErrorModule');
			define('AG_MODULE_DISABLED_ACTION', 'DisabledModule');
		}
		$controller = Context::getInstance()->getController();
		$controller->setRenderMode(View::RENDER_VAR);
		try {
			$mode = $controller->getRenderMode();
			$this->assertIdentical(View::RENDER_VAR, $mode);
			$controller->forward('UnavailableModule', 'Index');
			$lastActionEntry = $controller->getActionStack()->getLastEntry();
			$this->assertIsA($lastActionEntry, 'ActionStackEntry');
			$view = $lastActionEntry->getPresentation();
			$this->assertWantedPattern('/module has been disabled/i',$view);
			$module = $lastActionEntry->getModuleName();
			$action = $lastActionEntry->getActionName();
			$this->assertIdentical(AG_MODULE_DISABLED_MODULE, $module);
			$this->assertIdentical(AG_MODULE_DISABLED_ACTION, $action);
		} catch (ForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
	}

	public function testForwardingSuccessfully()
	{
		$context = Context::getInstance();
		$context->getController()->setRenderMode(View::RENDER_VAR);
		try {
			$context->getController()->forward('Test', 'Test');
			$lastActionEntry = $context->getActionStack()->getLastEntry();
			$this->assertIsA($lastActionEntry, 'ActionStackEntry');
			$view = $lastActionEntry->getPresentation();
			$this->assertWantedPattern('/test successful/i',$view);
			$module = $lastActionEntry->getModuleName();
			$action = $lastActionEntry->getActionName();
			$this->assertIdentical('Test', $module);
			$this->assertIdentical('Test', $action);
		} catch (ForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
		
	}

	public function testGetActionFromModule()
	{
		$action = Context::getInstance()->getController()->getAction('Test', 'Test');
		$this->assertIsA($action, 'Test_TestAction');
		$this->assertIsA($action, 'Action');
	}

	public function testGetActionStack()
	{
		$con_as = Context::getInstance()->getController()->getActionStack();
		$ctx_as = Context::getInstance()->getActionStack();
		$this->assertIsA($con_as, 'ActionStack');
		$this->assertIsA($ctx_as, 'ActionStack');
		$this->assertReference($ctx_as, $con_as);
	}

	public function testGetContext()
	{
		$ctx1 = Context::getInstance();
		$ctx2 = Context::getInstance()->getController()->getContext();
		$this->assertIsA($ctx1, 'Context');
		$this->assertIsA($ctx2, 'Context');
		$this->assertReference($ctx1, $ctx2);
	}

	public function testGetGlobalModel()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller->getGlobalModel('Sample'), 'SampleModel');
		$this->assertIsA($controller->getGlobalModel('SingletonSample'), 'SingletonSampleModel');
		$firstSingleton = $controller->getGlobalModel('SingletonSample');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getGlobalModel('SingletonSample');
		$this->assertEqual($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}
	
	public function testGetGlobalModel_recursive()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller->getGlobalModel('SampleRecursive'), 'SampleRecursiveModel');
		$this->assertIsA($controller->getGlobalModel('SingletonSampleRecursive'), 'SingletonSampleRecursiveModel');
		$firstSingleton = $controller->getGlobalModel('SingletonSampleRecursive');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getGlobalModel('SingletonSampleRecursive');
		$this->assertEqual($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testGetInstance()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller, 'Controller');
	}

	public function testGetModel()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller->getModel('Test', 'Test'), 'Test_TestModel');
		$this->assertIsA($controller->getModel('Test', 'Test2'), 'Test2Model');
		$this->assertIsA($controller->getModel('Test', 'SingletonTest'), 'Test_SingletonTestModel');
		$this->assertIsA($controller->getModel('Test', 'SingletonTest2'), 'SingletonTest2Model');
		$firstSingleton = $controller->getModel('Test', 'SingletonTest');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $controller->getModel('Test', 'SingletonTest');
		$this->assertEqual($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testSetGetRenderMode()
	{
		$controller = Context::getInstance()->getController();
		$this->assertEqual(View::RENDER_CLIENT, $controller->getRenderMode());
		
		$controller->setRenderMode(View::RENDER_VAR);
		$this->assertEqual(View::RENDER_VAR, $controller->getRenderMode());
		
		$controller->setRenderMode(View::RENDER_NONE);
		$this->assertEqual(View::RENDER_NONE, $controller->getRenderMode());
	}

	public function testGetView()
	{
		$controller = Context::getInstance()->getController();
		$this->assertIsA($controller->getView('Test', 'TestSuccess'), 'Test_TestSuccessView');
		$this->assertIsA($controller->getView('Test', 'TestError'), 'Test_TestErrorView');
	}

	public function testModelExists()
	{
		$controller = Context::getInstance()->getController();
		$this->assertTrue($controller->modelExists('Test', 'Test'));
		$this->assertFalse($controller->modelExists('Test', 'Bunk'));
		$this->assertFalse($controller->modelExists('Bunk', 'Bunk'));
	}

	public function testModuleExists()
	{
		$controller = Context::getInstance()->getController();
		$this->assertTrue($controller->moduleExists('Test'));
		$this->assertFalse($controller->moduleExists('Bunk'));
	}

	public function testSetRenderMode()
	{
		$controller = Context::getInstance()->getController();
		$good = array(View::RENDER_CLIENT, View::RENDER_VAR, VIEW::RENDER_NONE);
		$bad = array(932940, null, '');
		foreach ($good as &$value) {
			try {
				$controller->setRenderMode($value);
				$this->pass();
			} catch (RenderException $e) {
				$this->assertTrue(0,'Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$controller->setRenderMode($value);
				$this->assertTrue(0,'Expected RenderException not thrown!');
			} catch (RenderException $e) {
				$this->pass('Appropriately caught a bad render mode.');
			}
		}
	}

	public function testViewExists()
	{
		$controller = Context::getInstance()->getController();
		$this->assertTrue($controller->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($controller->viewExists('Bunk', 'Bunk'));
	}

	public function inCLI()
	{
		$controller = Context::getInstance()->getController();
		$this->assertEqual((php_sapi_name() == 'cli'), $controller->inCLI());
	}

}

?>
<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class ControllerTest extends UnitTestCase
{
	private $_controller = null, 
					$_context = null;

	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->setRenderMode(View::RENDER_VAR);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}

	public function testNewController()
	{
		$this->assertIsA($this->_controller, 'MockController');
		$this->assertIsA($this->_controller->getContext(), 'Context');
		$this->assertIsA($this->_context->getRequest(), 'WebRequest');
		
		if (defined('AG_USE_DATABASE') && AG_USE_DATABASE) {
			$this->assertIsA($this->_context->getDatabaseManager(), 'MockDatabaseManager');
		} else {
			$this->assertTrue(0,'Why is the Database Disabled?');
			$this->assertNull($this->_context->getDatabaseManager());
		}
		// View::RENDER_NONE(1), View::RENDER_CLIENT(2), View::RENDER_VAR(4)
		$this->assertEqual(View::RENDER_VAR, $this->_controller->getRenderMode());
	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem
		$this->assertTrue($this->_controller->actionExists('Test', 'Test'));
		$this->assertFalse($this->_controller->actionExists('Test', 'Bunk'));
		$this->assertFalse($this->_controller->actionExists('Bunk', 'Bunk'));
	}

	public function testforwardTooTheMaxThrowsException()
	{
		$max = defined('AG_MAX_FORWARDS') ? AG_MAX_FORWARDS : 3;
		// mock the actionStack
		Mock::generate('ActionStack');
		$myActionStack = new MockActionStack($this);
		$myActionStack->setReturnValue('getSize', $max);
		$myActionStack->expectOnce('getSize', array());
		$this->_context->replaceObj('actionStack', $myActionStack);
		try {
			$this->_controller->forward('Test', 'Test');
			$this->assertTrue(0,'Expected ForwardException not thrown');
		} catch (ForwardException $e) {
			$this->assertWantedPattern('/too many forwards/i', $e->getMessage());
		}
		$this->_context->getActionStack()->tally();
	}
	
	public function testCantForwardToUnconfiguredModule()
	{
		try {
			$this->_controller->forward('NoConfigModule', 'Some');
			$this->assertTrue(0,'Expected ParseException not thrown, there is only an empty module.ini there!');
		} catch (ParseException $e) {
			$this->assertWantedPattern('/missing/i', $e->getMessage());
		}
	}

	public function testForwardingToUnavailableModule()
	{
		try {
			$this->_controller->forward('UnavailableModule', 'Index');
			$lastActionEntry = $this->_context->getActionStack()->getLastEntry();
			$this->assertIsA($lastActionEntry, 'ActionStackEntry');
			$view = $lastActionEntry->getPresentation();
			$this->assertWantedPattern('/not available/i',$view);
			$mod = $lastActionEntry->getModuleName();
			$this->assertIdentical($mod, AG_MODULE_DISABLED_MODULE);
		} catch (ForwardException $e) {
			$this->assertTrue(0, 'Test forwarding to an unavilable module needs work');
		}
	}

	public function testForwardingSuccessfully()
	{
		$this->assertIsA($this->_controller->getActionStack(), 'ActionStack');
		try {
			$this->_controller->forward('Test', 'Test');
		} catch (Exception $e) {
			$this->fail('hullo');
		}
		
	}

	public function testgetAction()
	{
		$this->assertIsA($this->_controller->getAction('Test', 'Test'), 'Test_TestAction');
	}

	public function testgetActionStack()
	{
		$as = $this->_controller->getActionStack();
		$this->assertIsA($as, 'ActionStack');
	}

	public function testgetContext()
	{
		$c = $this->_controller->getContext();
		$this->assertIsA($c, 'Context');
	}

	public function testgetGlobalModel()
	{
		$this->assertIsA($this->_controller->getGlobalModel('Sample'), 'SampleModel');
	}
	
	public function testgetGlobalModel_recursive()
	{
		$this->assertIsA($this->_controller->getGlobalModel('SampleRecursive'), 'SampleRecursiveModel');
	}

	public function testgetInstance()
	{
		$this->assertTrue(0, 'Incomplete Test');
		/*
		try {
			MockController::getInstance();
			$this->assertTrue(0,'Expected ControllerException not thrown!');
		} catch (ControllerException $e) {
			$this->pass('Received the Controller exception we were expecting.');
		}
		$this->_controller = Controller::newInstance('ConsoleController');
		$this->assertIdentical($this->_controller, MockController::getInstance());
		*/
	}

	public function testgetModel()
	{
		$this->assertIsA($this->_controller->getModel('Test', 'Test'), 'Test_TestModel');
		$this->assertIsA($this->_controller->getModel('Test', 'Test2'), 'Test2Model');
	}

	public function testgetRenderMode()
	{
		$this->assertEqual(View::RENDER_VAR, $this->_controller->getRenderMode());
		$this->_controller->setRenderMode(View::RENDER_NONE);
		$this->assertEqual(View::RENDER_NONE, $this->_controller->getRenderMode());
	}

	public function testgetView()
	{
		$this->assertIsA($this->_controller->getView('Test', 'TestSuccess'), 'Test_TestSuccessView');
		$this->assertIsA($this->_controller->getView('Test', 'TestError'), 'TestErrorView');
	}

	public function testmodelExists()
	{
		$this->assertTrue($this->_controller->modelExists('Test', 'Test'));
		$this->assertFalse($this->_controller->modelExists('Test', 'Bunk'));
		$this->assertFalse($this->_controller->modelExists('Bunk', 'Bunk'));
	}

	public function testmoduleExists()
	{
		$this->assertTrue($this->_controller->moduleExists('Test'));
		$this->assertFalse($this->_controller->moduleExists('Bunk'));
	}

	public function testnewInstance()
	{

		/*
		$this->assertIsA(Controller::newInstance('MockController'), 'MockController');
		try {
			MockController::newInstance('Request');
			$this->assertTrue(0,'Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		try {
			MockController::newInstance('MockController');
			$this->assertTrue(0,'Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		*/
	}

	public function testsetRenderMode()
	{
		$good = array(View::RENDER_CLIENT, View::RENDER_VAR, VIEW::RENDER_NONE);
		$bad = array(932940, null, '');
		foreach ($good as &$value) {
			try {
				$this->_controller->setRenderMode($value);
				$this->pass();
			} catch (RenderException $e) {
				$this->assertTrue(0,'Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$this->_controller->setRenderMode($value);
				$this->assertTrue(0,'Expected RenderException not thrown!');
			} catch (RenderException $e) {
				$this->pass('Appropriately caught a bad render mode.');
			}
		}
	}

	public function testshutdown()
	{
	}

	public function testviewExists()
	{
		$this->assertTrue($this->_controller->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($this->_controller->viewExists('Test', 'Bunk'));
		$this->assertFalse($this->_controller->viewExists('Bunk', 'Bunk'));
	}

	public function inCLI()
	{
		$this->assertEqual((php_sapi_name() == 'cli'), $this->_controller->inCLI());
	}

}

?>

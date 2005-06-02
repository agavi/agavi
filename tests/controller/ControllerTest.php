<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class ControllerTest extends UnitTestCase
{
	private $_controller = null, 
					$_context = null;

	public function setUp()
	{
		$this->_controller = new MockController($this);
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
		$this->assertIsA($this->_context->getRequest(), 'MockWebRequest');
		
		if (defined('AG_USE_DATABASE') && AG_USE_DATABASE) {
			$this->assertIsA($this->_context->getDatabaseManager(), 'MockDatabaseManager');
		} else {
			$this->fail('Why is the Database Disabled');
			$this->assertNull($this->_context->getDatabaseManager());
		}
		
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
		// tell our mocked actionstack to lie about it's size
		$this->_context->getActionStack()->setReturnValue('getSize', 3);
		$this->_context->getActionStack()->expectOnce('getSize', array());
		try {
			$this->_controller->forward('Test', 'Test');
			$this->Fail('Expected ForwardException not thrown');
		} catch (ForwardException $e) {
			$this->assertWantedPattern('/too many forwards/i', $e->getMessage());
		}
		$this->_context->getActionStack()->tally();
	}
	
	public function testCantForwardToUnconfiguredModule()
	{
		try {
			$this->_controller->forward('NoConfigModule', 'SomeAction');
			$this->Fail('Expected ConfigurationException not thrown, there is only an empty module.ini there!');
		} catch (ConfigurationException $e) {
			$this->assertWantedPattern('/unreadable/i', $e->getMessage());
		}
	}

	public function testForwardingToUnavailableModule()
	{
		$this->fail("Fixme: Setup a module in the fs and configure it to not be available, 
		also configure Unavailable action under Error module for AG_MODULE_DISABLED_MODULE/ACTION pair settings.
		");
	}

	public function testForwardingSuccessfully()
	{
		// we actually want a real actionstack in place now.
		MockContext::useRealActionStack();
		$this->assertIsA($this->_controller->getActionStack(), 'ActionStack');
		try {
			$this->_controller->forward('Test', 'Test');
		} catch (Exception $e) {
		}
		
	}

	public function testgetAction()
	{
		$this->assertIsA($this->_controller->getAction('Test', 'Test'), 'Test_TestAction');
	}

	public function testgetActionStack()
	{
		$as = $this->_controller->getActionStack();
		$this->assertIsA($as, 'MockActionStack');
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
		$this->fail("Controllers shouldnt be singletons, down with the singleton controllers!
		see: http://trac.agavi.org/trac.cgi/wiki/SmellsAndItches\n");
		/*
		try {
			MockController::getInstance();
			$this->fail('Expected ControllerException not thrown!');
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
		$this->assertEqual(View::RENDER_CLIENT, $this->_controller->getRenderMode());
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
			$this->fail('Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		try {
			MockController::newInstance('MockController');
			$this->fail('Expected FactoryException not thrown!');
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
				$this->fail('Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$this->_controller->setRenderMode($value);
				$this->fail('Expected RenderException not thrown!');
			} catch (RenderException $e) {
				$this->pass();
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

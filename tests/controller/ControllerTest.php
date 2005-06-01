<?php

require_once dirname(__FILE__) . '/../mockContext.php';

class ControllerTest extends UnitTestCase
{
	private $_c = null;

	public function setUp()
	{
		$this->_c = new MockController($this);
		$this->_c->dispatch();
	}

	public function tearDown()
	{
		$this->_c = null;
	}

	public function testNewController()
	{
		define('AG_USE_DATABASE', true);
		$this->assertTrue($this->_c instanceof MockController);
		$context = $this->_c->getContext();
		$this->assertTrue($context instanceof Context);
	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem
		$this->assertTrue($this->_c->actionExists('Test', 'Test'));
		$this->assertFalse($this->_c->actionExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->actionExists('Bunk', 'Bunk'));
	}

	public function testforward()
	{
		try {
			$this->_c->forward('Default', 'SOMEACTIONTHATSURELYDONUTEXIST');
		} catch (ConfigurationException $e) {
			$this->Pass('Correctly caught unconfigured exception.');
		} catch (ForwardException $e) {
			$this->Pass('Correctly caught forward exception when forwarding to bunk action.');
		}
	}

	public function testgetAction()
	{
		$this->assertIsA($this->_c->getAction('Test', 'Test'), 'Test_TestAction');
	}

	public function testgetActionStack()
	{
		$as = $this->_c->getActionStack();
		$this->assertTrue($as instanceof MockActionStack);
	}

	public function testgetContext()
	{
		$c = $this->_c->getContext();
		$this->assertTrue($c instanceof Context);
	}

	public function testgetGlobalModel()
	{
		$this->assertIsA($this->_c->getGlobalModel('Sample'), 'SampleModel');
	}
	
	public function testgetGlobalModel_recursive()
	{
		$this->assertIsA($this->_c->getGlobalModel('SampleRecursive'), 'SampleRecursiveModel');
	}

	public function testgetInstance()
	{
		try {
			MockController::getInstance();
			$this->fail('Expected ControllerException not thrown!');
		} catch (ControllerException $e) {
			$this->pass();
		}
		$this->_c = Controller::newInstance('MockController');
		$this->assertIdentical($this->_c, MockController::getInstance());
	}

	public function testgetModel()
	{
		$this->assertIsA($this->_c->getModel('Test', 'Test'), 'Test_TestModel');
		$this->assertIsA($this->_c->getModel('Test', 'Test2'), 'Test2Model');
	}

	public function testgetRenderMode()
	{
		$this->assertEqual(View::RENDER_CLIENT, $this->_c->getRenderMode());
		$this->_c->setRenderMode(View::RENDER_NONE);
		$this->assertEqual(View::RENDER_NONE, $this->_c->getRenderMode());
	}

	public function testgetView()
	{
		$this->assertIsA($this->_c->getView('Test', 'TestSuccess'), 'Test_TestSuccessView');
		$this->assertIsA($this->_c->getView('Test', 'TestError'), 'TestErrorView');
	}

	public function testmodelExists()
	{
		$this->assertTrue($this->_c->modelExists('Test', 'Test'));
		$this->assertFalse($this->_c->modelExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->modelExists('Bunk', 'Bunk'));
	}

	public function testmoduleExists()
	{
		$this->assertTrue($this->_c->moduleExists('Test'));
		$this->assertFalse($this->_c->moduleExists('Bunk'));
	}

	public function testnewInstance()
	{
		/* Since we're testing a singleton here, this set of tests fail since 
		 * we already called newInstance() in an earlier test.
		 * 
		 * How do we fix this?
		 */

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
				$this->_c->setRenderMode($value);
				$this->pass();
			} catch (RenderException $e) {
				$this->fail('Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$this->_c->setRenderMode($value);
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
		$this->assertTrue($this->_c->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($this->_c->viewExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->viewExists('Bunk', 'Bunk'));
	}

	public function inCLI()
	{
		$this->assertEqual((php_sapi_name() == 'cli'), $this->_c->inCLI());
	}

}

?>

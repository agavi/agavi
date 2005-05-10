<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('util/Toolkit.class.php');
require_once('controller/Controller.class.php');
require_once('exception/AgaviException.class.php');
require_once('exception/ControllerException.class.php');
require_once('exception/FactoryException.class.php');
require_once('exception/RenderException.class.php');
require_once('view/View.class.php');

// setup some defines
define('MO_MODULE_DIR', dirname(__file__).'/sandbox/modules/');
define('MO_LIB_DIR', dirname(__file__).'/sandbox/lib');

// generate our Mocks

class SampleController extends Controller {}

class ControllerTest extends UnitTestCase
{
	private $_c = null;


	public function setUp()
	{
		$this->_c = new SampleController();
	}

	public function testactionExists()
	{
		$this->assertTrue($this->_c->actionExists('Test', 'Test'));
		$this->assertFalse($this->_c->actionExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->actionExists('Bunk', 'Bunk'));
	}

	public function testforward()
	{
		$this->fail('Incomplete Test');
	}

	public function testgetAction()
	{
		$this->assertIsA($this->_c->getAction('Test', 'Test'), 'Test_TestAction');
	}

	public function testgetActionStack()
	{
		$this->fail('Incomplete Test - depends on initialize() test');
	}

	public function testgetContext()
	{
		$this->fail('Incomplete Test - depends on initialize() test');
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
			SampleController::getInstance();
			$this->fail('Expected ControllerException not thrown!');
		} catch (ControllerException $e) {
			$this->pass();
		}
		$this->_c = Controller::newInstance('SampleController');
		$this->assertIdentical($this->_c, SampleController::getInstance());
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

	public function testInitialize()
	{
		$this->fail('Incomplete Test');
	}

	public function testloadGlobalFilters()
	{
		$this->fail('Incomplete Test');
	}

	public function testloadModuleFilters()
	{
		$this->fail('Incomplete Test');
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
		$this->assertIsA(Controller::newInstance('SampleController'), 'SampleController');
		try {
			SampleController::newInstance('Request');
			$this->fail('Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		try {
			SampleController::newInstance('SampleController');
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

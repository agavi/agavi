<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('controller/Controller.class.php');
require_once('exception/AgaviException.class.php');
require_once('exception/ControllerException.class.php');
require_once('view/View.class.php');

// setup some defines
define('MO_MODULE_DIR', dirname(__file__).'/sandbox/modules/');
define('MO_LIB_DIR', dirname(__file__).'/sandbox/lib');

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
		$this->fail('Incomplete Test');
	}

	public function testgetContext()
	{
		$this->fail('Incomplete Test');
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
			$this->fail();
		} catch (ControllerException $e) { }
		$this->_c = SampleController::newInstance('SampleController');
		$this->assertIdentical($this->_c, SampleController::getInstance());
	}

	public function testgetModel()
	{
		$this->assertIsA($this->_c->getModel('Test', 'Test'), 'Test_TestModel');
		$this->assertIsA($this->_c->getModel('Test', 'Test2'), 'Test2Model');
	}

	public function testgetRenderMode()
	{
		$this->fail('Incomplete Test');
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
		$this->fail('Incomplete Test');
	}

	public function testsetRenderMode()
	{
		$this->fail('Incomplete Test');
	}

	public function testshutdown()
	{
		$this->fail('Incomplete Test');
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

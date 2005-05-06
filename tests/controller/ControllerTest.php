<?php
require_once('tests/include.php');
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('controller/Controller.class.php');
require_once('exception/AgaviException.class.php');
require_once('exception/ControllerException.class.php');

// setup some defines
define('MO_MODULE_DIR', dirname(__file__).'/sandbox/modules/');
define('MO_LIB_DIR', dirname(__file__).'/sandbox/lib');

class SampleController extends Controller {}

class ControllerTest extends PHPUnit2_Framework_TestCase
{
	private $_c = null;

	public function setUp()
	{
		$this->_c = new SampleController();
	}

	public function testactionExists()
	{
		self::assertTrue($this->_c->actionExists('Test', 'Test'));
		self::assertFalse($this->_c->actionExists('Test', 'Bunk'));
		self::assertFalse($this->_c->actionExists('Bunk', 'Bunk'));
	}

	public function testforward()
	{
	}

	public function testgetAction()
	{
		self::assertTrue($this->_c->getAction('Test', 'Test') instanceof Test_TestAction);
	}

	public function testgetActionStack()
	{
	}

	public function testgetContext()
	{
	}

	public function testgetGlobalModel()
	{
		self::assertTrue($this->_c->getGlobalModel('Sample') instanceof SampleModel);
	}
	
	public function testgetGlobalModel_recursive()
	{
		self::assertTrue($this->_c->getGlobalModel('SampleRecursive') instanceof SampleRecursiveModel);
	}

	public function testgetInstance()
	{
		try {
			SampleController::getInstance();
			self::fail();
		} catch (ControllerException $e) { }
		$this->_c = SampleController::newInstance('SampleController');
		self::assertSame(SampleController::getInstance(), $this->_c);
	}

	public function testgetModel()
	{
		self::assertTrue($this->_c->getModel('Test', 'Test') instanceof Test_TestModel);
		self::assertTrue($this->_c->getModel('Test', 'Test2') instanceof Test2Model);
	}

	public function testgetRenderMode()
	{
	}

	public function testgetView()
	{
		self::assertTrue($this->_c->getView('Test', 'TestSuccess') instanceof Test_TestSuccessView);
		self::assertTrue($this->_c->getView('Test', 'TestError') instanceof TestErrorView);
	}

	public function testInitialize()
	{
	}

	public function testloadGlobalFilters()
	{
	}

	public function testloadModuleFilters()
	{
	}

	public function testmodelExists()
	{
		self::assertTrue($this->_c->modelExists('Test', 'Test'));
		self::assertFalse($this->_c->modelExists('Test', 'Bunk'));
		self::assertFalse($this->_c->modelExists('Bunk', 'Bunk'));
	}

	public function testmoduleExists()
	{
		self::assertTrue($this->_c->moduleExists('Test'));
		self::assertFalse($this->_c->moduleExists('Bunk'));
	}

	public function testnewInstance()
	{
	}

	public function testsetRenderMode()
	{
	}

	public function testshutdown()
	{
	}

	public function testviewExists()
	{
		self::assertTrue($this->_c->viewExists('Test', 'TestSuccess'));
		self::assertFalse($this->_c->viewExists('Test', 'Bunk'));
		self::assertFalse($this->_c->viewExists('Bunk', 'Bunk'));
	}

	public function inCLI()
	{
		self::assertEquals($this->_c->inCLI(), (php_sapi_name() == 'cli'));
	}

}

?>

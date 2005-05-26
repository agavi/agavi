<?php
require_once('core/AgaviObject.class.php');
require_once('core/Context.class.php');
require_once('util/ParameterHolder.class.php');
require_once('controller/Controller.class.php');
require_once('config/ParameterParser.class.php');
require_once('view/View.class.php');
require_once('request/Request.class.php');
require_once('action/Action.class.php');

class ActionTestSampleController extends Controller {
	public function dispatch() {}
}

class SampleAction extends Action {
	public function execute() {}
}

class TestAction extends UnitTestCase
{
	private $_a = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_a = new SampleAction();
		$this->_controller = new ActionTestSampleController();
		$this->_context = Context::getInstance($this->_controller);
	}

	public function testgetContext()
	{
		$this->_a->initialize($this->_context);
		$c = $this->_a->getContext();
		$this->assertReference($this->_context, $c);
	}

	public function testgetCredential()
	{
		$this->assertNull($this->_a->getCredential());
	}

	public function testgetDefaultView()
	{
		$this->assertEqual(View::INPUT, $this->_a->getDefaultView());
	}

	public function testgetRequestMethods()
	{
		$this->assertEqual((Request::GET | Request::POST | Request::NONE), $this->_a->getRequestMethods());
	}

	public function testhandleError()
	{
		$this->assertEqual(View::ERROR, $this->_a->handleError());
	}

	public function testinitialize()
	{
		$this->assertTrue($this->_a->initialize($this->_context));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_a->isSecure());
	}

	public function testregisterValidators()
	{
		$this->fail('Incomplete Test, unimplemented method.');
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_a->validate());
	}
}
?>

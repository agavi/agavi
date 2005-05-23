<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('view/View.class.php');
require_once('request/Request.class.php');
require_once('action/Action.class.php');

class SampleAction extends Action {
	public function execute() {}
}

class Context extends AgaviObject {}

class TestAction extends UnitTestCase
{
	private $_a = null;

	public function setUp()
	{
		$this->_a = new SampleAction();
	}

	public function testgetContext()
	{
		$context = new Context();
		$this->_a->initialize($context);
		$this->assertReference($context, $this->_a->getContext());
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
		$context = new Context();
		$this->assertTrue($this->_a->initialize($context));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_a->isSecure());
	}

	public function testregisterValidators()
	{
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_a->validate());
	}
}
?>

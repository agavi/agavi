<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class SampleAction extends Action {
	public function execute() {}
}

class TestAction extends UnitTestCase
{
	private $_action = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
		
		$this->_action = new SampleAction();
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_action = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}

	public function testgetContext()
	{
		$this->_action->initialize($this->_context);
		$c = $this->_action->getContext();
		$this->assertReference($this->_context, $c);
	}

	public function testgetCredential()
	{
		$this->assertNull($this->_action->getCredential());
	}

	public function testgetDefaultView()
	{
		$this->assertEqual(View::INPUT, $this->_action->getDefaultView());
	}

	public function testgetRequestMethods()
	{
		$this->assertEqual((Request::GET | Request::POST | Request::NONE), $this->_action->getRequestMethods());
	}

	public function testhandleError()
	{
		$this->assertEqual(View::ERROR, $this->_action->handleError());
	}

	public function testinitialize()
	{
		$this->assertTrue($this->_action->initialize($this->_context));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_action->isSecure());
	}

	public function testregisterValidators()
	{
		$this->fail('Incomplete Test, unimplemented method. YAGNI?');
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_action->validate());
	}
}
?>

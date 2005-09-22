<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class SampleAction extends Action {
	public function execute() {}

	// public function registerValidator($validationManager){}
}

class TestAction extends UnitTestCase
{
	private $_action = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_context = Context::getInstance();
		$this->_controller = $this->_context->getController();
		
		$this->_action = new SampleAction();
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_action = null;
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
		// FIXME:
		// This method is meant to be implemented in the implemented action, if required. 
		// We should provide an example implementation and test. 
		$this->assertTrue(0,'registerValidators method not yet implemented, create an example implementation!');
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_action->validate());
	}
}
?>

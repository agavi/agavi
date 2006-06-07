<?php

class SampleAction extends AgaviAction {
	public function execute() {}

	// public function registerValidator($validationManager){}
}

class TestAction extends AgaviTestCase
{
	private $_action = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_context = AgaviContext::getInstance();
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

	public function testgetCredentials()
	{
		$this->assertNull($this->_action->getCredentials());
	}

	public function testgetDefaultViewName()
	{
		$this->assertEquals(AgaviView::INPUT, $this->_action->getDefaultViewName());
	}

	public function testgetRequestMethods()
	{
		$this->assertEquals((AgaviRequest::GET | AgaviRequest::POST | AgaviRequest::NONE), $this->_action->getRequestMethods());
	}

	public function testhandleError()
	{
		$this->assertEquals(AgaviView::ERROR, $this->_action->handleError());
	}

	public function testinitialize()
	{
		$this->assertTrue($this->_action->initialize($this->_context));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_action->isSecure());
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_action->validate());
	}
}
?>
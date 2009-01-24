<?php

class SampleAction extends AgaviAction {
	public function execute(AgaviParameterHolder $parameters)
	{
	}
}

class ActionTest extends AgaviTestCase
{
	private $_action = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_context = AgaviContext::getInstance('test');
		$this->_controller = $this->_context->getController();

		$this->_action = new SampleAction();
		$this->_action->initialize($this->_controller->createExecutionContainer('Foo', 'Bar'));
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_action = null;
		$this->_context = null;
	}

	public function testgetContext()
	{
		$c = $this->_action->getContext();
		$this->assertReference($this->_context, $c);
	}

	public function testCredentials()
	{
		$this->assertNull($this->_action->getCredentials());
	}

	public function testgetDefaultViewName()
	{
		$this->assertEquals('Input', $this->_action->getDefaultViewName());
	}

	public function testhandleError()
	{
		$this->assertEquals('Error', $this->_action->handleError(new AgaviRequestDataHolder()));
	}

	public function testisSecure()
	{
		$this->assertFalse($this->_action->isSecure());
	}

	public function testvalidate()
	{
		$this->assertTrue($this->_action->validate(new AgaviRequestDataHolder()));
	}
}
?>
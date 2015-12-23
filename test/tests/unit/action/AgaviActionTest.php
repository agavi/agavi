<?php

class SampleAction extends AgaviAction {
	public function execute(AgaviParameterHolder $parameters)
	{
	}
}

class ActionTest extends AgaviUnitTestCase
{
	private $_action = null;

	public function setUp()
	{
		$this->_action = new SampleAction();
		$this->_action->initialize($this->getContext()->getController()->createExecutionContainer('Foo', 'Bar'));
	}

	public function tearDown()
	{
		$this->_action = null;
	}

	public function testgetContext()
	{
		$context = $this->getContext();
		$actionContext = $this->_action->getContext();
		$this->assertSame($context, $actionContext);
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
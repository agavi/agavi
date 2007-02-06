<?php

class SampleRequest extends AgaviRequest
{
	public function shutdown() {}
}

class RequestTest extends AgaviTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new SampleRequest();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testgetInstance()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx_test = $this->_r->getContext();
		$this->assertReference($ctx, $ctx_test);
	}

	public function testSetGetMethod()
	{
		$this->assertNull($this->_r->getMethod());
		$this->_r->setMethod('Get');
		$this->assertEquals('Get', $this->_r->getMethod());
	}

	public function testGetModuleAccessor()
	{
		$this->assertEquals('module', $this->_r->getModuleAccessor());
		$this->_r->initialize(AgaviContext::getInstance('test'), array('module_accessor' => 'moduleTest'));
		$this->assertEquals('moduleTest', $this->_r->getModuleAccessor());
	}

	public function testGetActionAccessor()
	{
		$this->assertEquals('action', $this->_r->getActionAccessor());
		$this->_r->initialize(AgaviContext::getInstance('test'), array('action_accessor' => 'actionTest'));
		$this->assertEquals('actionTest', $this->_r->getActionAccessor());
	}
}
?>
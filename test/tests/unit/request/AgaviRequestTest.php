<?php

class SampleRequest extends AgaviRequest
{
	public function shutdown() {}
}

class AgaviRequestTest extends AgaviUnitTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new SampleRequest();
		$this->_r->initialize($this->getContext());
	}

	public function testgetInstance()
	{
		$ctx = $this->getContext();
		$ctx_test = $this->_r->getContext();
		$this->assertSame($ctx, $ctx_test);
	}

	public function testSetGetMethod()
	{
		$this->assertNull($this->_r->getMethod());
		$this->_r->setMethod('Get');
		$this->assertEquals('Get', $this->_r->getMethod());
	}

	public function testGetModuleAccessor()
	{
		$this->assertEquals('module', $this->_r->getParameter('module_accessor'));
		$this->_r->initialize($this->getContext(), array('module_accessor' => 'moduleTest'));
		$this->assertEquals('moduleTest', $this->_r->getParameter('module_accessor'));
	}

	public function testGetActionAccessor()
	{
		$this->assertEquals('action', $this->_r->getParameter('action_accessor'));
		$this->_r->initialize($this->getContext(), array('action_accessor' => 'actionTest'));
		$this->assertEquals('actionTest', $this->_r->getParameter('action_accessor'));
	}
}
?>
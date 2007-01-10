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
		$this->_r->getContext()->getValidationManager()->clear();
	}

	public function testgetInstance()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx_test = $this->_r->getContext();
		$this->assertReference($ctx, $ctx_test);
	}

	public function testGetError()
	{
		$this->_r->setError('blah', 'blahval');
		$this->assertEquals('blahval', $this->_r->getError('blah'));
		$this->assertNull($this->_r->getError('bunk'));
	}

	public function testGetErrorNames()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->assertEquals(array('blah', 'blah2'), $this->_r->getErrorNames());
	}

	public function testGetErrors()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->_r->setError('blah2', 'blah2val2');
		$errorsEx = array(
			'blah' => array('messages' => array('blahval'), 'validators' => array()),
			'blah2' => array('messages' => array('blah2val', 'blah2val2'), 'validators' => array()),
		);

		$this->assertEquals($errorsEx, $this->_r->getErrors());
	}

	public function testSetGetMethod()
	{
		$this->assertNull($this->_r->getMethod());
		$this->_r->setMethod('Get');
		$this->assertEquals('Get', $this->_r->getMethod());
	}

	
	public function testHasError()
	{
		$this->assertFalse($this->_r->hasError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertTrue($this->_r->hasError('blah'));
		$this->assertFalse($this->_r->hasError('bunk'));
	}

	public function testHasErrors()
	{
		$this->assertFalse($this->_r->hasErrors());
		$this->_r->setError('blah', 'blahval');
		$this->assertTrue($this->_r->hasErrors());
	}
/*
	public function testRemoveError()
	{
		$this->assertNull($this->_r->removeError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertEquals(array('messages' => array('blahval'), 'validators' => array()), $this->_r->removeError('blah'));
		$this->assertNull($this->_r->removeError('blah'));
	}
*/
	public function testSetError()
	{
		$this->_r->setError('blah', 'blahval');
		$this->assertEquals('blahval', $this->_r->getError('blah'));
	}

	public function testSetErrors()
	{
		$this->_r->setErrors(array('blah'=>'blahval'));
		$this->assertEquals('blahval', $this->_r->getError('blah'));
		$this->_r->setErrors(array('blah2'=>'blah2val'));
		$this->assertEquals('blahval', $this->_r->getError('blah'));
		$this->assertEquals('blah2val', $this->_r->getError('blah2'));
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
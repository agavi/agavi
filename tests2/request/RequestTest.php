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
		$this->_r->initialize(AgaviContext::getInstance());
	}

	public function testGetInstance()
	{
		$this->assertReference(AgaviContext::getInstance(), $this->_r->getContext());
	}

	public function testExtractParameters()
	{
		$p = array(
			'One' => '1',
			'Two' => 'Too',
			'Three' => '3eee',
			'Four' => 'foh');
		$this->_r->setParameters($p);
		$this->assertEquals(array('One'=>'1'), $this->_r->extractParameters(array('One')));
		$this->assertEquals(array('Two'=>'Too'), $this->_r->extractParameters(array('Two')));
		$this->assertEquals(array('Four'=>'foh', 'Five' => null), $this->_r->extractParameters(array('Four','Five')));
		
		// what happens if we forget to contain the args within an array? ;) 
		// - Since it's casted to an array it snags only the first arg
		$this->assertEquals(array('heh'=>null), $this->_r->extractParameters('heh'));
		$this->assertEquals(array('heh'=>null), $this->_r->extractParameters('heh', 'hah'));
		$this->assertEquals(array(), $this->_r->extractParameters(array()));
		$this->assertEquals(array('One'=>'1'), $this->_r->extractParameters(array('One')));

		// Test that we're working with references
		$ref1	= $this->_r->extractParameters(array('One'));
		$ref2 = $this->_r->extractParameters(array('One'));
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertEquals($ref1['One'], $ref2['One']);
		
		$ref1['One'] = 'Wun';
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertEquals($ref1['One'], $ref2['One']);
		
		$this->_r->setParameter('One', 'AndOnly');
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertEquals($ref1['One'], $ref2['One']);
		
		$ref3 = $this->_r->extractParameters(array('One'));
		$this->assertEquals($ref1['One'], $ref3['One']);
		$this->assertReference($ref1['One'], $ref3['One']);
		$this->assertEquals($ref2['One'], $ref3['One']);
		$this->assertEquals('AndOnly', $ref3['One']);
		$this->assertEquals('AndOnly', $ref1['One']);
		
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
		$this->assertEquals(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getErrors());
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

	public function testRemoveError()
	{
		$this->assertNull($this->_r->removeError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertEquals('blahval', $this->_r->removeError('blah'));
		$this->assertNull($this->_r->removeError('blah'));
	}

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
		$this->_r->initialize(AgaviContext::getInstance(), array('module_accessor' => 'moduleTest'));
		$this->assertEquals('moduleTest', $this->_r->getModuleAccessor());
	}

	public function testGetActionAccessor()
	{
		$this->assertEquals('action', $this->_r->getActionAccessor());
		$this->_r->initialize(AgaviContext::getInstance(), array('action_accessor' => 'actionTest'));
		$this->assertEquals('actionTest', $this->_r->getActionAccessor());
	}
}
?>
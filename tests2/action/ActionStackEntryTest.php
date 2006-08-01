<?php

class ASESampleAction extends AgaviAction
{
	public function execute() {}
}

class ActionStackEntryTest extends AgaviTestCase
{
	private
		$_a   = null,
		$_ase = null,
		$_t = 0;

	public function setUp()
	{
		$this->_a = new ASESampleAction();
		$this->_t = microtime(true);
		$this->_ase = new AgaviActionStackEntry('Sample', 'Index', $this->_a, new AgaviParameterHolder(array('foo' => 'foo', 'bar' => 'bar')));
	}

	public function testgetActionName()
	{
		$this->assertEquals('Index', $this->_ase->getActionName());
	}
	
	public function testgetActionInstance()
	{
		$a = $this->_ase->getActionInstance();
		$this->assertReference($this->_a, $a);
	}

	public function testgetMicrotime()
	{
		$mt = $this->_ase->getMicrotime();
		$this->assertNotNull($mt);
		$this->assertTrue(is_float($mt));
		$this->assertTrue($this->_t < $mt);
	}

	public function testgetModuleName()
	{
		$this->assertEquals('Sample', $this->_ase->getModuleName());
	}

	public function testgetsetPresentation()
	{
		$this->assertNull($this->_ase->getPresentation());
		$p = new AgaviWebResponse();
		$this->_ase->setPresentation($p);
		$p_test = $this->_ase->getPresentation();
		$this->assertReference($p, $p_test);
	}
	
	public function testgetsetParameters()
	{
		$this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $this->_ase->getParameters()->getParameters());
		$this->_ase->setParameters(new AgaviParameterHolder(array('baz' => 'baz')));
		$this->assertEquals(array('baz' => 'baz'), $this->_ase->getParameters()->getParameters());
	}
}
?>
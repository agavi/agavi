<?php

class ASESampleAction extends AgaviAction
{
	public function execute() {}
}

class ActionStackEntryTest extends AgaviTestCase
{
	private
		$_a   = null,
		$_ase = null;

	public function setUp()
	{
		$this->_a = new ASESampleAction();
		$this->_ase = new AgaviActionStackEntry('Sample', 'Index', $this->_a);
	}

	public function testNewActionStackEntry()
	{
		$this->assertTrue($this->_ase instanceof AgaviActionStackEntry);
	}

	public function testgetActionName()
	{
		$this->assertEquals('Index', $this->_ase->getActionName());
	}
	
	public function testgetActionInstance()
	{
		$this->assertReference($this->_a, $this->_ase->getActionInstance());
	}

	public function testgetMicrotime()
	{
		$mt = $this->_ase->getMicrotime();
		$this->assertNotNull($mt);
		$this->assertTrue( is_string($mt) );
	}

	public function testgetModuleName()
	{
		$this->assertEquals('Sample', $this->_ase->getModuleName());
	}

	public function testgetsetPresentation()
	{
		$this->assertNull($this->_ase->getPresentation());
		$p = 'bill';
		$this->_ase->setPresentation($p);
		$this->assertReference($p, $this->_ase->getPresentation());
	}
}
?>
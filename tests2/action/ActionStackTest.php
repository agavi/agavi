<?php

class ASSampleAction extends AgaviAction
{
	public function execute() {}
}

class ActionStackTest extends AgaviTestCase
{
	private
		$_a   = null,
		$_as = null;

	public function setUp()
	{
		$this->_a = new ASSampleAction();
		$this->_as = new AgaviActionStack();
	}

	public function testaddEntry()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$ase = $this->_as->getEntry(0);
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a, $ase->getActionInstance());
	}

	public function testgetEntry()
	{
		//covered by testaddEntry()
	}

	public function testgetFirstEntry()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$ase = $this->_as->getFirstEntry();
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a, $ase->getActionInstance());
	}

	public function testgetLastEntry()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$ase = $this->_as->getLastEntry();
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a, $ase->getActionInstance());
	}

	public function testgetSize()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$this->assertEquals(1, $this->_as->getSize());
	}

}
?>
<?php

class ASSampleAction extends AgaviAction
{
	public function execute() {}
}

class ActionStackTest extends AgaviTestCase
{
	private
		$_a1   = null,
		$_a2   = null,
		$_a3   = null,
		$_as   = null;

	public function setUp()
	{
		$this->_a1 = new ASSampleAction();
		$this->_a2 = new ASSampleAction();
		$this->_a3 = new ASSampleAction();
		$this->_as = new AgaviActionStack();

		$this->_as->addEntry('Sample', 'Index', $this->_a1);
		$this->_as->addEntry('Sample', 'Index2', $this->_a2);
		$this->_as->addEntry('Sample', 'Index3', $this->_a3);
	}

	public function testaddEntry()
	{
		$a = new ASSampleAction();
		$this->_as->addEntry('Sample1', 'Index4', $a);
		$ase = $this->_as->getEntry(3);
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertEquals('Sample1', $ase->getModuleName());
		$this->assertEquals('Index4', $ase->getActionName());
		$this->assertReference($a, $ase->getActionInstance());
	}

	public function testgetEntry()
	{
		$ase = $this->_as->getEntry(0);
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a1, $ase->getActionInstance());
		$this->assertReference($this->_a2, $this->_as->getEntry(1)->getActionInstance());
		$this->assertReference($this->_a3, $this->_as->getEntry(2)->getActionInstance());

		// check for null return on invalid inputs
		$this->assertNull($this->_as->getEntry(-1));
		$this->assertNull($this->_as->getEntry(4));
	}

	public function testgetFirstEntry()
	{
		$ase = $this->_as->getFirstEntry();
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a1, $ase->getActionInstance());

		// check for null return on empty chain 
		$as = new AgaviActionStack();
		$this->assertNull($as->getFirstEntry());
	}

	public function testgetLastEntry()
	{
		$ase = $this->_as->getLastEntry();
		$this->assertType('AgaviActionStackEntry', $ase);
		$this->assertReference($this->_a3, $ase->getActionInstance());

		// check for null return on empty chain 
		$as = new AgaviActionStack();
		$this->assertNull($as->getLastEntry());
	}

	public function testgetSize()
	{
		$this->assertEquals(3, $this->_as->getSize());
	}

}
?>
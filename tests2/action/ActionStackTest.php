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
		$_as   = null,
		$_pars = null;

	public function setUp()
	{
		$this->_a1 = new ASSampleAction();
		$this->_a2 = new ASSampleAction();
		$this->_a3 = new ASSampleAction();
		$this->_as = new AgaviActionStack();
		$this->_pars = new AgaviParameterHolder(array('foo' => 'foo', 'bar' => 'bar'));
		
		$this->_as->addEntry('Sample', 'Index', $this->_a1, $this->_pars);
		$this->_as->addEntry('Sample', 'Index2', $this->_a2, $this->_pars);
		$this->_as->addEntry('Sample', 'Index3', $this->_a3, $this->_pars);
	}

	public function testaddEntry()
	{
		$a = new ASSampleAction();
		$this->_as->addEntry('Sample1', 'Index4', $a, $this->_pars);
		$ase = $this->_as->getEntry(3);
		$this->assertInstanceOf('AgaviActionStackEntry', $ase);
		$this->assertEquals('Sample1', $ase->getModuleName());
		$this->assertEquals('Index4', $ase->getActionName());
		$this->assertEquals($this->_pars, $ase->getParameters());
		$ai = $ase->getActionInstance();
		$this->assertReference($a, $ai);
	}

	public function testgetEntry()
	{
		$ase = $this->_as->getEntry(0);
		$this->assertInstanceOf('AgaviActionStackEntry', $ase);
		$a1 = $ase->getActionInstance();
		$a2 = $this->_as->getEntry(1)->getActionInstance();
		$a3 = $this->_as->getEntry(2)->getActionInstance();
		$this->assertReference($this->_a1, $a1);
		$this->assertReference($this->_a2, $a2);
		$this->assertReference($this->_a3, $a3);

		// check for null return on invalid inputs
		$this->assertNull($this->_as->getEntry(-1));
		$this->assertNull($this->_as->getEntry(4));
	}

	public function testgetFirstEntry()
	{
		$ase = $this->_as->getFirstEntry();
		$this->assertInstanceOf('AgaviActionStackEntry', $ase);
		$a = $ase->getActionInstance();
		$this->assertReference($this->_a1, $a);

		// check for null return on empty chain 
		$as = new AgaviActionStack();
		$this->assertNull($as->getFirstEntry());
	}

	public function testgetLastEntry()
	{
		$ase = $this->_as->getLastEntry();
		$this->assertInstanceOf('AgaviActionStackEntry', $ase);
		$a = $ase->getActionInstance();
		$this->assertReference($this->_a3, $a);

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
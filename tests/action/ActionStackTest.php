<?php
require_once('core/AgaviObject.class.php');
require_once('action/Action.class.php');
require_once('action/ActionStackEntry.class.php');
require_once('action/ActionStack.class.php');

class ASSampleAction extends Action
{
	public function execute() {}
}

class ActionStackTest extends UnitTestCase
{
	private
		$_a   = null,
		$_as = null;

	public function setUp()
	{
		$this->_a = new ASSampleAction();
		$this->_as = new ActionStack();
	}

	public function testaddEntry()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$ase = $this->_as->getEntry(0);
		$this->assertIsA($ase, 'ActionStackEntry');
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
		$this->assertIsA($ase, 'ActionStackEntry');
		$this->assertReference($this->_a, $ase->getActionInstance());
	}

	public function testgetLastEntry()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$ase = $this->_as->getLastEntry();
		$this->assertIsA($ase, 'ActionStackEntry');
		$this->assertReference($this->_a, $ase->getActionInstance());
	}

	public function testgetSize()
	{
		$this->_as->addEntry('Sample', 'Index', $this->_a);
		$this->assertEqual(1, $this->_as->getSize());
	}

}
?>

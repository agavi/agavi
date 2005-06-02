<?php
require_once('core/AgaviObject.class.php');
require_once('action/Action.class.php');
require_once('action/ActionStackEntry.class.php');

class ASESampleAction extends Action
{
	public function execute() {}
}

class ActionStackEntryTest extends UnitTestCase
{
	private
		$_a   = null,
		$_ase = null;

	public function setUp()
	{
		$this->_a = new ASESampleAction();
		$this->_ase = new ActionStackEntry('Sample', 'Index', $this->_a);
	}

	public function testNewActionStackEntry()
	{
		$this->assertTrue($this->_ase instanceof ActionStackEntry);
	}

	public function testgetActionName()
	{
		$this->assertIdentical('Index', $this->_ase->getActionName());
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
		$this->assertEqual('Sample', $this->_ase->getModuleName());
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

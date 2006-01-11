<?php
require_once dirname(__FILE__) . '/../test_environment.php';
class PseudoValidator extends Validator
{
	public function execute(&$value, &$error)
	{
		return true;
	}
}

class TestValidatorManager extends UnitTestCase 
{
	private $_vm = null,
					$_controller = null,
					$_context = null;
	
	public function setUp()
	{
		$this->_context = Context::getInstance();
		$this->_controller = $this->_context->getController();
		
		$this->_vm = new ValidatorManager();
		$this->_vm->initialize($this->_context);		
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_vm = null;
		$this->_context = null;
	}


	public function testinitialize()
	{
		$context = Context::getInstance();
		$vm = new ValidatorManager();
		$this->assertIsA($this->_vm, 'ValidatorManager');
		
		$vm->initialize($this->_context);
		$groups = $vm->getGroups();
		$names = $vm->getNames();
		$this->assertEqual(0, count($groups));
		$this->assertEqual(0, count($names));
		$this->assertTrue(is_array($names));
		$this->assertTrue(is_array($groups));
	}

	public function testregisterName()
	{
		// Clear the validator & check that names
		// 1) is an empty array
		$this->_vm->clear();
		$this->assertEqual($this->_vm->getNames(), array(), 'name cleared');
		// Register a name check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		$name = "My Test #1";
		$this->_vm->registerName($name);
		$names = $this->_vm->getNames();
		$this->assertIsA($names,'Array', 'name registered');
		$this->assertNotEqual($names, array(), 'names established');
		$this->assertIsA($names[$name], 'Array', 'name registered');
		$this->assertNull($names[$name]['group'], 'group entry default');
		$this->assertFalse($names[$name]['is_file'], 'is_file entry default');
		$this->assertTrue($names[$name]['required'], 'required entry default');
		$this->assertEqual($names[$name]['required_msg'], 'Required', 'required_msg entry default');
		$this->assertTrue($names[$name]['validation_status'], 'validation_status entry default');
		$this->assertEqual($names[$name]['validators'], array(), 'validators entry default');
		// Register an optional name check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		$name = "My Test #2";
		$this->_vm->registerName($name,false,'Optional',null,null,false);
		$names = $this->_vm->getNames();
		$this->assertIsA($names,'Array', 'name registered');
		$this->assertNotEqual($names, array(), 'names established');
		$this->assertIsA($names[$name], 'Array', 'name registered');
		$this->assertNull($names[$name]['group'], 'group entry default');
		$this->assertFalse($names[$name]['is_file'], 'is_file entry default');
		$this->assertFalse($names[$name]['required'], 'required entry default');
		$this->assertEqual($names[$name]['required_msg'], 'Optional', 'required_msg entry default');
		$this->assertTrue($names[$name]['validation_status'], 'validation_status entry default');
		$this->assertEqual($names[$name]['validators'], array(), 'validators entry default');
		// Register a required child name
		$nameParent = "Apa";
		$nameChild = "My Chils Test #1";
		$this->_vm->registerName($nameChild,true,'Required',$nameParent,null,false);
		// check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		$names = $this->_vm->getNames();
		$this->assertIsA($names,'Array', 'name registered');
		$this->assertNotEqual($names[$nameParent], array(), 'names established');
		$this->assertIsA($names[$nameParent][$nameChild], 'Array', 'name registered');
		$this->assertNull($names[$nameParent][$nameChild]['group'], 'group entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['is_file'], 'is_file entry default');
		$this->assertTrue($names[$nameParent][$nameChild]['required'], 'required entry default');
		$this->assertEqual($names[$nameParent][$nameChild]['required_msg'], 'Required', 'required_msg entry default');
		$this->assertTrue($names[$nameParent][$nameChild]['validation_status'], 'validation_status entry default');
		$this->assertEqual($names[$nameParent][$nameChild]['validators'], array(), 'validators entry default');
		// Register a second optional child name
		$nameParent = "Apa";
		$nameChild = "My Child Test #2";
		$this->_vm->registerName($nameChild,false,'Optional',$nameParent,null,false);
		// check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		// 10) the parent array contains two entries
		$names = $this->_vm->getNames();
		$this->assertIsA($names,'Array', 'name registered');
		$this->assertNotEqual($names[$nameParent], array(), 'names established');
		$this->assertIsA($names[$nameParent][$nameChild], 'Array', 'name registered');
		$this->assertNull($names[$nameParent][$nameChild]['group'], 'group entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['is_file'], 'is_file entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['required'], 'required entry');
		$this->assertEqual($names[$nameParent][$nameChild]['required_msg'], 'Optional', 'required_msg entry');
		$this->assertTrue($names[$nameParent][$nameChild]['validation_status'], 'validation_status entry default');
		$this->assertEqual($names[$nameParent][$nameChild]['validators'], array(), 'validators entry default');
		$this->assertEqual(count($names[$nameParent]), 3, 'parent array has _is_parent + 2 entries');

		$name = "My Test #3";
		$group = 'TestGroup';
		$this->_vm->registerName($name,false,'Optional',null,$group,false);
		$groups = $this->_vm->getGroups();
		$this->assertTrue(isset($groups[$group]));
		$this->assertFalse($groups[$group]['_force']);
		$this->assertIdentical($name, $groups[$group][0]);
	}	// end of testregisterName()
	

	public function testregisterValidator()
	{
		$validator = new PseudoValidator();
		$this->_vm->registerValidator('pseudo', $validator);
		$names = $this->_vm->getNames();
		$this->assertEqual(1, count($names));
		$this->assertTrue(isset($names['pseudo']));
		
	}

	public function testexecute()
	{
		$validator = new PseudoValidator();
		$this->_vm->registerValidator('pseudo', $validator);
		$names = $this->_vm->getNames();
		$this->assertEqual(1, count($names));
		$this->assertTrue(isset($names['pseudo']));
		$this->assertTrue($this->_vm->execute());
	}

	public function testclear()
	{
		// Check that the groups and names arrays have been cleared.
		$this->_vm->clear();
		$this->assertEqual($this->_vm->getGroups(), array(), 'groups cleared');
		$this->assertEqual($this->_vm->getNames(),  array(), 'names cleared');
	}	// end of testClear()
	
}
?>
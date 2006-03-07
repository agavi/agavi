<?php

class PseudoValidator extends AgaviValidator
{
	public function execute(&$value, &$error)
	{
		return true;
	}
}

class TestValidatorManager extends AgaviTestCase 
{
	private $_vm = null,
					$_context = null;
	
	public function setUp()
	{
		$this->_context = AgaviContext::getInstance();
		
		$this->_vm = $this->_context->getValidatorManager();
	}

	public function tearDown()
	{
		$this->_vm = null;
		$this->_context = null;
	}

	public function testregisterName()
	{
		// Clear the validator & check that names
		// 1) is an empty array
		$this->_vm->clear();
		$this->assertEquals($this->_vm->getNames(), array(), 'name cleared');
		// Register a name check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		$name = "My Test #1";
		$this->_vm->registerName($name);
		$names = $this->_vm->getNames();
		$this->assertType('array', $names, 'name registered');
		$this->assertNotEquals($names, array(), 'names established');
		$this->assertType('array', $names[$name], 'name registered');
		$this->assertNull($names[$name]['group'], 'group entry default');
		$this->assertFalse($names[$name]['is_file'], 'is_file entry default');
		$this->assertTrue($names[$name]['required'], 'required entry default');
		$this->assertEquals($names[$name]['required_msg'], 'Required', 'required_msg entry default');
		$this->assertTrue($names[$name]['validation_status'], 'validation_status entry default');
		$this->assertEquals($names[$name]['validators'], array(), 'validators entry default');
		// Register an optional name check that names:
		// 1) is an array
		// 2) is not an empty array
		// 3) the name has been registered
		// 4) - 9) register entries are set up correctly
		$name = "My Test #2";
		$this->_vm->registerName($name,false,'Optional',null,null,false);
		$names = $this->_vm->getNames();
		$this->assertType('array', $names, 'name registered');
		$this->assertNotEquals($names, array(), 'names established');
		$this->assertType('array', $names[$name], 'name registered');
		$this->assertNull($names[$name]['group'], 'group entry default');
		$this->assertFalse($names[$name]['is_file'], 'is_file entry default');
		$this->assertFalse($names[$name]['required'], 'required entry default');
		$this->assertEquals($names[$name]['required_msg'], 'Optional', 'required_msg entry default');
		$this->assertTrue($names[$name]['validation_status'], 'validation_status entry default');
		$this->assertEquals($names[$name]['validators'], array(), 'validators entry default');
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
		$this->assertType('array', $names, 'name registered');
		$this->assertNotEquals($names[$nameParent], array(), 'names established');
		$this->assertType('array', $names[$nameParent][$nameChild], 'name registered');
		$this->assertNull($names[$nameParent][$nameChild]['group'], 'group entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['is_file'], 'is_file entry default');
		$this->assertTrue($names[$nameParent][$nameChild]['required'], 'required entry default');
		$this->assertEquals($names[$nameParent][$nameChild]['required_msg'], 'Required', 'required_msg entry default');
		$this->assertTrue($names[$nameParent][$nameChild]['validation_status'], 'validation_status entry default');
		$this->assertEquals($names[$nameParent][$nameChild]['validators'], array(), 'validators entry default');
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
		$this->assertType('array', $names, 'name registered');
		$this->assertNotEquals($names[$nameParent], array(), 'names established');
		$this->assertType('array', $names[$nameParent][$nameChild], 'name registered');
		$this->assertNull($names[$nameParent][$nameChild]['group'], 'group entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['is_file'], 'is_file entry default');
		$this->assertFalse($names[$nameParent][$nameChild]['required'], 'required entry');
		$this->assertEquals($names[$nameParent][$nameChild]['required_msg'], 'Optional', 'required_msg entry');
		$this->assertTrue($names[$nameParent][$nameChild]['validation_status'], 'validation_status entry default');
		$this->assertEquals($names[$nameParent][$nameChild]['validators'], array(), 'validators entry default');
		$this->assertEquals(count($names[$nameParent]), 3, 'parent array has _is_parent + 2 entries');

		$name = "My Test #3";
		$group = 'TestGroup';
		$this->_vm->registerName($name,false,'Optional',null,$group,false);
		$groups = $this->_vm->getGroups();
		$this->assertTrue(isset($groups[$group]));
		$this->assertFalse($groups[$group]['_force']);
		$this->assertEquals($name, $groups[$group][0]);
	}	// end of testregisterName()
	

	public function testregisterValidator()
	{
		$this->_vm->clear();
		$validator = new PseudoValidator();
		$this->_vm->registerValidator('pseudo', $validator);
		$names = $this->_vm->getNames();
		$this->assertEquals(1, count($names));
		$this->assertTrue(isset($names['pseudo']));
		
	}

	public function testexecute()
	{
		$validator = new PseudoValidator();
		$this->_vm->registerValidator('pseudo', $validator);
		$names = $this->_vm->getNames();
		$this->assertEquals(1, count($names));
		$this->assertTrue(isset($names['pseudo']));
		$this->assertTrue($this->_vm->execute());
	}

	public function testclear()
	{
		// Check that the groups and names arrays have been cleared.
		$this->_vm->clear();
		$this->assertEquals($this->_vm->getGroups(), array(), 'groups cleared');
		$this->assertEquals($this->_vm->getNames(),  array(), 'names cleared');
	}	// end of testClear()
	
}
?>
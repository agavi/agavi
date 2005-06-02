<?php
require_once dirname(__FILE__) . '/../mockContext.php';
require_once 'validator/ValidatorManager.class.php';

class TestValidatorManager extends UnitTestCase 
{
	private $_vm = null,
					$_controller = null,
					$_context = null;
	
	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
		
		$this->_vm = new ValidatorManager();
		$this->_vm->initialize($this->_context);
	}

	public function tearDown()
	{
		$this->_controller = null;
		$this->_vm = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}


	public function testinitialize()
	{
		$this->assertIsA($this->_vm, 'ValidatorManager');
	}

	public function testregisterName()
	{
		$this->fail('Incomplete Test');
	}

	public function testregisterValidator()
	{
		$this->fail('Incomplete Test');
	}

	public function testexecute()
	{
		$this->fail('Incomplete Test');
	}

	public function testclear()
	{
		$this->fail('Incomplete Test');
	}
	
}

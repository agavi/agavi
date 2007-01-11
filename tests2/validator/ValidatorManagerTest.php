<?php

require_once(dirname(__FILE__).'/inc/DummyValidator.class.php');

class MyValidatorManager extends getValidationManager
{
	public function getChildren() { return $this->Children; }
}

class ValidationManagerTest extends AgaviTestCase 
{
	private $_vm = null;
	private $_context = null;
	
	public function setUp()
	{
		$this->_context = AgaviContext::getInstance('test');
		$this->_vm = $this->_context->getValidationManager();
	}

	public function tearDown()
	{
		$this->_vm = null;
		$this->_context = null;
	}
	
	public function testgetContext()
	{
		$this->assertSame($this->_vm->getContext(), $this->_context);
	}
	
	public function testclear()
	{
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$val = new DummyValidator($vm, array());
		$vm->addChild($val);
		
		$this->assertFalse($val->shutdown);
		$vm->clear();
		$this->assertTrue($val->shutdown);
		$this->assertEquals($vm->getChildren(), array());
	}
	
	public function testaddChild()
	{
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$val = new DummyValidator($vm, array());

		$this->assertEquals($vm->getChildren(), array());
		$vm->addChild($val);
		$this->assertEquals($vm->getChildren(), array($val));
	}
	
	public function testgetRequest()
	{
		$this->assertSame($this->_vm->getRequest(), $this->_context->getRequest());
	}
	
	public function testgetErrorManager()
	{
		$this->assertTrue($this->_vm->getErrorManager() instanceof AgaviErrorManager);
	}
 
	public function testgetDependencyManager()
	{
		$this->assertTrue($this->_vm->getDependencyManager() instanceof AgaviDependencyManager);
	}
	
	public function testgetBase()
	{
		$this->_vm->removeParameter('base');
		$this->assertEquals($this->_vm->getBase(), '/');
		$this->_vm->setParameter('base', '');
		$this->assertEquals($this->_vm->getBase(), '');
		$this->_vm->setParameter('base', '/foo/bar');
		$this->assertEquals($this->_vm->getBase(), '/foo/bar');
	}
	
	public function testexecute()
	{
		$val1 = new DummyValidator($this->_vm, array());
		$val2 = new DummyValidator($this->_vm, array());
		
		$val1->val_result = true;
		$val2->val_result = true;
		
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertTrue($this->_vm->execute());
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();

		$val1->val_result = false;
		$val1->setParameter('severity', 'none');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertTrue($this->_vm->execute());
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'error');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertFalse($this->_vm->execute());
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'critical');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertFalse($this->_vm->execute());
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
	}
	
	public function testshutdown()
	{
		$val = new DummyValidator($this->_vm, array());
		$this->_vm->addChild($val);
		
		$this->assertFalse($val->shutdown);
		$this->_vm->shutdown();
		$this->assertTrue($val->shutdown);
	}
	
	public function testregisterValidators()
	{
		$val1 = new DummyValidator($this->_vm, array());
		$val2 = new DummyValidator($this->_vm, array());
		
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$this->assertEquals($vm->getChildren(), array());
		$vm->registerValidators(array($val1, $val2));
		$this->assertEquals($vm->getChildren(), array($val1, $val2));
	}
	
	public function testgetErrorArrayByValidator()
	{
		$this->assertEquals($this->_vm->getErrorArrayByValidator(), $this->_vm->getErrorManager()->getErrorArrayByValidator());
	}
	
	public function testgetErrorArrayByInput()
	{
		$this->assertEquals($this->_vm->getErrorArrayByInput(), $this->_vm->getErrorManager()->getErrorArrayByInput());
	}
	
	public function testgetErrorMessage()
	{
		$this->assertEquals($this->_vm->getErrorMessage(), $this->_vm->getErrorManager()->getErrorMessage());
	}

	public function testgetResult()
	{
		$this->assertEquals($this->_vm->getResult(), $this->_vm->getErrorManager()->getResult());
	}
}
?>
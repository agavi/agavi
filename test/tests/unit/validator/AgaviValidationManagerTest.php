<?php

class MyValidationManager extends AgaviValidationManager
{
	public function getChildren() { return $this->children; }
}

class AgaviValidationManagerTest extends AgaviUnitTestCase 
{
	private $_vm = null;
	private $_context = null;
	
	public function setUp()
	{
		$this->_context = $this->getContext();
		$this->_vm = $this->_context->createInstanceFor('validation_manager');
	}

	public function tearDown()
	{
		$this->_vm = null;
		$this->_context = null;
	}
	
	public function testGetContext()
	{
		$this->assertSame($this->_vm->getContext(), $this->_context);
	}
	
	public function testClear()
	{
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$val = $vm->createValidator('DummyValidator', array());
		
		$this->assertFalse($val->shutdown);
		$vm->clear();
		$this->assertTrue($val->shutdown);
		$this->assertEquals($vm->getChildren(), array());
	}
	
	public function testAddChild()
	{
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$val = new DummyValidator();
		$val->initialize($this->getContext(), array('name' => 'val'));

		$this->assertEquals($vm->getChildren(), array());
		$vm->addChild($val);
		$this->assertEquals($vm->getChildren(), array('val' => $val));
	}
	
	public function testgetDependencyManager()
	{
		$this->assertTrue($this->_vm->getDependencyManager() instanceof AgaviDependencyManager);
	}
	
	public function testgetBase()
	{
		$this->_vm->removeParameter('base');
		$this->assertEquals($this->_vm->getBase(), new AgaviVirtualArrayPath(''));
		$this->_vm->setParameter('base', '');
		$this->assertEquals($this->_vm->getBase(), new AgaviVirtualArrayPath(''));
		$this->_vm->setParameter('base', 'foo[bar]');
		$this->assertEquals($this->_vm->getBase(), new AgaviVirtualArrayPath('foo[bar]'));
	}
	
	public function testExecute()
	{
		$val1 = $this->_vm->createValidator('DummyValidator', array());
		$val2 = $this->_vm->createValidator('DummyValidator', array());
		
		$val1->val_result = true;
		$val2->val_result = true;
		
		$this->assertTrue($this->_vm->execute(new AgaviRequestDataHolder()));
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();

		$val1->val_result = false;
		$val1->setParameter('severity', 'none');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertTrue($this->_vm->execute(new AgaviRequestDataHolder()));
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'error');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertFalse($this->_vm->execute(new AgaviRequestDataHolder()));
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'critical');
		$this->_vm->registerValidators(array($val1, $val2));
		$this->assertFalse($this->_vm->execute(new AgaviRequestDataHolder()));
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		$this->_vm->clear();
		$val1->clear();
		$val2->clear();
	}
	
	public function testShutdown()
	{
		$val = $this->_vm->createValidator('DummyValidator', array());
		
		$this->assertFalse($val->shutdown);
		$this->_vm->shutdown();
		$this->assertTrue($val->shutdown);
	}
	
	public function testRegisterValidators()
	{
		$val1 = $this->_vm->createValidator('DummyValidator', array(), array(), array('name' => 'val1'));
		$val2 = $this->_vm->createValidator('DummyValidator', array(), array(), array('name' => 'val2'));
		
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$this->assertEquals($vm->getChildren(), array());
		$vm->registerValidators(array($val1, $val2));
		$this->assertEquals($vm->getChildren(), array('val1' => $val1, 'val2' => $val2));
	}
	
	public function testGetResult()
	{
		$this->assertEquals(AgaviValidator::NOT_PROCESSED, $this->_vm->getResult());
	}
	
	public function testTransfersDependTokens()
	{
		$vm = new MyValidationManager;
		$vm->initialize($this->_context);
		$validator = $this->_vm->createValidator('DummyValidator', array(), array(), array('provides' => 'provide-token'));
		$vm->registerValidators(array($validator));
		$vm->execute(new AgaviRequestDataHolder());
		$this->assertEquals(array('provide-token' => true), $vm->getReport()->getDependTokens());
	}
}
?>
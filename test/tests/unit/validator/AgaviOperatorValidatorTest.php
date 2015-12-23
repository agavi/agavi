<?php

class MyOperatorValidator extends AgaviOperatorValidator
{
	public $checked = false;
	
	protected function validate() {return true;}
	protected function checkValidSetup() {$this->checked = true;}
	public function getChildren() {return $this->children;}
}

class AgaviOperatorValidatorTest extends AgaviUnitTestCase
{
	private $context;
	private $vm;
	
	public function setUp()
	{
		$this->context = $this->getContext();
		$this->vm = $this->context->createInstanceFor('validation_manager');
	}
	
	public function tearDown()
	{
		$this->vm = null;
		$this->context = null;
	}
	
	public function testShutdown()
	{
		$val = $this->vm->createValidator('DummyValidator', array());
		$v = $this->vm->createValidator('MyOperatorValidator', array());
		$v->addChild($val);
		
		$this->assertFalse($val->shutdown);
		$v->shutdown();
		$this->assertTrue($val->shutdown);
	}
	
	public function testRegisterValidators()
	{
		$val1 = $this->vm->createValidator('DummyValidator', array(), array(), array('name' => 'val1'));
		$val2 = $this->vm->createValidator('DummyValidator', array(), array(), array('name' => 'val2'));
		
		$v = $this->vm->createValidator('MyOperatorValidator', array(), array(), array());
		$this->assertEquals($v->getChildren(), array());
		$v->registerValidators(array($val1, $val2));
		$this->assertEquals($v->getChildren(), array('val1' => $val1, 'val2' =>$val2));
	}
	
	public function testAddChild()
	{
		$val = $this->vm->createValidator('DummyValidator', array(), array(), array('name' => 'val'));
		$v = $this->vm->createValidator('MyOperatorValidator', array());

		$this->assertEquals($v->getChildren(), array());
		$v->addChild($val);
		$this->assertEquals($v->getChildren(), array('val' => $val));
	}
	
	public function testExecute()
	{
		$v = $this->vm->createValidator('MyOperatorValidator', array());
		$this->assertFalse($v->checked);
		$this->assertEquals($v->execute(new AgaviRequestDataHolder()), AgaviValidator::SUCCESS);
		$this->assertTrue($v->checked);
	}
}
?>

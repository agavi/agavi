<?php

require_once(dirname(__FILE__).'/inc/DummyValidator.class.php');

class MyOperatorValidator extends AgaviOperatorValidator
{
	public $checked = false;
	
	protected function validate() {return true;}
	protected function checkValidSetup() {$this->checked = true;}
	public function getChildren() {return $this->Children;}
}

class OperatorValidatorTest extends AgaviTestCase
{
	private $context;
	private $vm;
	
	public function setUp()
	{
		$this->context = AgaviContext::getInstance();
		$this->vm = $this->context->getValidationManager();
	}
	
	public function tearDown()
	{
		$this->vm = null;
		$this->context = null;
	}
	
	public function testconstruct()
	{
		$v = new MyOperatorValidator($this->vm, array(), array(), array());
		$this->assertSame($v->getErrorManager(), $this->vm->getErrorManager());
		
		$v = new MyOperatorValidator($this->vm, array(), array(), array('skip_errors' => true));
		$this->assertNotSame($v->getErrorManager(), $this->vm->getErrorManager());
	}
	
	public function testshutdown()
	{
		$val = new DummyValidator($this->vm, array());
		$v = new MyOperatorValidator($this->vm, array());
		$v->addChild($val);
		
		$this->assertFalse($val->shutdown);
		$v->shutdown();
		$this->assertTrue($val->shutdown);
	}
	
	public function testregisterValidators()
	{
		$val1 = new DummyValidator($this->vm, array());
		$val2 = new DummyValidator($this->vm, array());
		
		$v = new MyOperatorValidator($this->vm, array(), array(), array());
		$this->assertEquals($v->getChildren(), array());
		$v->registerValidators(array($val1, $val2));
		$this->assertEquals($v->getChildren(), array($val1, $val2));
	}
	
	public function testaddChild()
	{
		$val = new DummyValidator($this->vm, array());
		$v = new MyOperatorValidator($this->vm, array(), array(), array());

		$this->assertEquals($v->getChildren(), array());
		$v->addChild($val);
		$this->assertEquals($v->getChildren(), array($val));
	}
	
	public function testgetRequest()
	{
		$v = new MyOperatorValidator($this->vm, array(), array(), array());
		$this->assertSame($v->getRequest(), $this->context->getRequest());
	}
	
	public function testgetErrorManager()
	{
		$v = new MyOperatorValidator($this->vm, array(), array(), array());
		$this->assertTrue($v->getErrorManager() instanceof AgaviErrorManager);
	}
 
	public function testgetBase()
	{
		$this->vm->removeParameter('base');
		$v = new MyOperatorValidator($this->vm, array());
		$this->assertEquals($v->getBase(), '/');
		
		$this->vm->setParameter('base', '');
		$v = new MyOperatorValidator($this->vm, array());
		$this->assertEquals($v->getBase(), '');
		
		$this->vm->setParameter('base', '/foo/bar');
		$v = new MyOperatorValidator($this->vm, array());
		$this->assertEquals($v->getBase(), '/foo/bar');
	}
	
	public function testexecute()
	{
		$v = new MyOperatorValidator($this->vm, array());
		$this->assertFalse($v->checked);
		$this->assertEquals($v->execute(), AgaviValidator::SUCCESS);
		$this->assertTrue($v->checked);
	}
}
?>

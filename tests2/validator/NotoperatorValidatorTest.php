<?php

require_once(dirname(__FILE__). '/inc/DummyValidator.class.php');

class NotoperatorValidatorTest extends AgaviTestCase
{
	public function testvalidate()
	{
		$vm = AgaviContext::getInstance()->getValidationManager();
		$vm->clear();
		$o = new AgaviNotoperatorValidator($vm, array(), array(), array('severity' => 'error'));
		
		$val1 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$o->registerValidators(array($val1));
		
		// 1st test: successful
		$val1->val_result = true;
		$this->assertEquals($o->execute(), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$val1->clear();

		// 2nd test: failure
		$val1->val_result = false;
		$this->assertEquals($o->execute(), AgaviValidator::SUCCESS);
		$this->assertTrue($val1->validated);
		$val1->clear();

		// 3rd test: critical
		$val1->val_result = false;
		$val1->setParameter('severity', 'critical');
		$this->assertEquals($o->execute(), AgaviValidator::CRITICAL);
		$this->assertTrue($val1->validated);
		$val1->clear();
	}
	
	public function testcheckValidSetup()
	{
		$vm = AgaviContext::getInstance()->getValidationManager();
		$vm->clear();
		$o = new AgaviNotoperatorValidator($vm, array(), array(), array('severity' => 'error'));
		
		$val1 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$val2 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		
		try {
			$o->execute();
			$this->fail();
		} catch(AgaviValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'NOT allows only 1 child validator');
		}
		$o->addChild($val1);
		
		$o->addChild($val2);
		try {
			$o->execute();
			$this->fail();
		} catch(AgaviValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'NOT allows only 1 child validator');
		}
	}
}
?>

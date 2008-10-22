<?php

require_once(dirname(__FILE__). '/inc/DummyValidator.class.php');

class AndoperatorValidatorTest extends AgaviTestCase
{
	public function testvalidate()
	{
		$vm = AgaviContext::getInstance()->getValidationManager();
		$vm->clear();
		$o = new AgaviAndoperatorValidator($vm, array(), array(), array('severity' => 'error'));
		
		$val1 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$val1->val_result = true;
		$val2 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$val2->val_result = true;
		
		$o->registerValidators(array($val1, $val2));
		
		$this->assertEquals($o->execute(), AgaviValidator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val1->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', true);
		$val1->val_result = false;
		
		$this->assertEquals($o->execute(), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', false);
		
		$this->assertEquals($o->execute(), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'critical');
		
		$this->assertEquals($o->execute(), AgaviValidator::CRITICAL);
		$this->assertEquals($vm->getResult(), AgaviValidator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
	}
}
?>

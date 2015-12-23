<?php

class AgaviAndoperatorValidatorTest extends AgaviUnitTestCase
{
	public function testExecute()
	{
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$vm->clear();
		$o = $vm->createValidator('AgaviAndoperatorValidator', array(), array(), array('severity' => 'error'));
		
		$val1 = $vm->createValidator('DummyValidator', array(), array(), array('severity' => 'error'));
		$val1->val_result = true;
		$val2 = $vm->createValidator('DummyValidator', array(), array(), array('severity' => 'error'));
		$val2->val_result = true;
		
		$o->registerValidators(array($val1, $val2));
		
		$this->assertEquals($o->execute(new AgaviRequestDataHolder()), AgaviValidator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val1->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', true);
		$val1->val_result = false;
		
		$this->assertEquals($o->execute(new AgaviRequestDataHolder()), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$o->setParameter('break', false);
		
		$this->assertEquals($o->execute(new AgaviRequestDataHolder()), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		
		$val1->clear();
		$val2->clear();
		
		$val1->setParameter('severity', 'critical');
		
		$this->assertEquals($o->execute(new AgaviRequestDataHolder()), AgaviValidator::CRITICAL);
		$this->assertEquals($vm->getResult(), AgaviValidator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
	}
}
?>

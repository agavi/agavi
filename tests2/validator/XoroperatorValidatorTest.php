<?php

require_once(dirname(__FILE__). '/inc/DummyValidator.class.php');

class XoroperatorValidatorTest extends AgaviTestCase
{
	public function testvalidate()
	{
		$vm = AgaviContext::getInstance()->getValidationManager();
		$vm->clear();
		$o = new AgaviXoroperatorValidator($vm, array(), array(), array('severity' => 'error'));
		
		$val1 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$val2 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$o->registerValidators(array($val1, $val2));
		
		// 1st test: both successful
		$val1->val_result = true;
		$val2->val_result = true;
		$this->assertEquals($o->execute(), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 2nd test: first successful
		$val1->val_result = true;
		$val2->val_result = false;
		$this->assertEquals($o->execute(), AgaviValidator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 3rd test: last successful
		$val1->val_result = false;
		$val2->val_result = true;
		$this->assertEquals($o->execute(), AgaviValidator::SUCCESS);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 4th test: none successful
		$val1->val_result = false;
		$val2->val_result = false;
		$this->assertEquals($o->execute(), AgaviValidator::ERROR);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();

		// 5th test: first results critical
		$val1->val_result = false;
		$val1->setParameter('severity', 'critical');
		$val2->val_result = true;
		$this->assertEquals($o->execute(), AgaviValidator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertFalse($val2->validated);
		$val1->setParameter('severity', 'error');
		$val1->clear();
		$val2->clear();

		// 5th test: last results critical
		$val1->val_result = true;
		$val2->val_result = false;
		$val2->setParameter('severity', 'critical');
		$this->assertEquals($o->execute(), AgaviValidator::CRITICAL);
		$this->assertTrue($val1->validated);
		$this->assertTrue($val2->validated);
		$val1->clear();
		$val2->clear();
	}
	
	public function testcheckValidSetup()
	{
		$vm = AgaviContext::getInstance()->getValidationManager();
		$vm->clear();
		$o = new AgaviXoroperatorValidator($vm, array(), array(), array('severity' => 'error'));
		
		$val1 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		$val2 = new DummyValidator($vm, array(), array(), array('severity' => 'error'));
		
		$o->addChild($val1);
		try {
			$o->execute();
			$this->fail();
		} catch(AgaviValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'XOR allows only exact 2 child validators');
		}
		
		$o->addChild($val2);
		
		$o->addChild($val1);
		try {
			$o->execute();
			$this->fail();
		} catch(AgaviValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'XOR allows only exact 2 child validators');
		}
	}
}
?>

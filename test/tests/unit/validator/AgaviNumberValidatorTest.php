<?php

class AgaviNumberValidatorTest extends AgaviUnitTestCase
{

	/**
	 * @var AgaviValidationManager
	 */
	protected $vm;

	public function setUp()
	{
		$this->vm = $this->getContext()->createInstanceFor('validation_manager');
	}
	
	public function testNoCastOnFail()
	{
		$number = '1.23';
		$validator = $this->vm->createValidator('AgaviNumberValidator', array('number'), array('invalid argument'), $parameters = array('type' => 'int'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('number' => $number)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::ERROR, $result);
		$this->assertEquals($number, $rd->getParameter('number'));
		$this->assertTrue(is_string($rd->getParameter('number')), 'Failed asserting that the parameter "number" is a string');
	}
	
	public function testImplicitCastToFloat()
	{
		$number = '1.23';
		$validator = $this->vm->createValidator('AgaviNumberValidator', array('number'), array('invalid argument'), $parameters = array('type' => 'float'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('number' => $number)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result);
		$this->assertEquals($number, $rd->getParameter('number'));
		$this->assertTrue(is_float($rd->getParameter('number')), 'Failed asserting that the parameter "number" is a float');
	}
	
	public function testImplicitCastToInt()
	{
		$number = '1';
		$validator = $this->vm->createValidator('AgaviNumberValidator', array('number'), array('invalid argument'), $parameters = array('type' => 'int'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('number' => $number)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result);
		$this->assertEquals($number, $rd->getParameter('number'));
		$this->assertTrue(is_int($rd->getParameter('number')), 'Failed asserting that the parameter "number" is an int');
	}
	
	public function testExplicitCastToInt()
	{
		$number = '1.23';
		$validator = $this->vm->createValidator('AgaviNumberValidator', array('number'), array('invalid argument'), $parameters = array('type' => 'float', 'cast_to' => 'int'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('number' => $number)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result);
		$this->assertEquals(1, $rd->getParameter('number'));
		$this->assertTrue(is_int($rd->getParameter('number')), 'Failed asserting that the parameter "number" is an int');
	}
	
	public function testExplicitCastToFloat()
	{
		$number = '1';
		$validator = $this->vm->createValidator('AgaviNumberValidator', array('number'), array('invalid argument'), $parameters = array('type' => 'float', 'cast_to' => 'float'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('number' => $number)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result);
		$this->assertEquals(1, $rd->getParameter('number'));
		$this->assertTrue(is_float($rd->getParameter('number')), 'Failed asserting that the parameter "number" is a float');
	}
	
}

?>
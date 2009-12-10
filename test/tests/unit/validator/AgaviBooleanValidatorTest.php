<?php

class AgaviBooleanValidatorTest extends AgaviUnitTestCase
{

	/**
	 * @var AgaviValidationManager
	 */
	protected $vm;

	public function setUp()
	{
		$this->vm = $this->getContext()->createInstanceFor('validation_manager');
	}
	
	/**
	 * @dataProvider validValues
	 */
	public function testAccept($value, $expectedResult)
	{
		$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'), $parameters = array('export' => 'exported'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result, 'Failed asserting that the validation succeeded.');
		$this->assertEquals($expectedResult, $rd->getParameter('exported'), 'Failed asserting that the exported value is the expected value');
	}

	public function validValues() {
		
		return array(
			'yes' => array('yes', true),
			'no' => array('no', false),
			'true' => array('true', true),
			'false' => array('false', false),
			'on' => array('on', true),
			'off' => array('off', false),
			'(bool)true' => array(true, true),
			'(bool)false' => array(false, false),
			'(int)1' => array(1, true),
			'(int)0' => array(0, false),
			'(string)1' => array('1', true),
			'(string)0' => array('0', false)
		);
		
	}
	
	/**
	 * @dataProvider invalidValues
	 */
	public function testNotAccept($value)
	{
		$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'), $parameters = array('export' => 'exported'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::ERROR, $result, 'Failed asserting that the validation failed.');
		$this->assertNull($rd->getParameter('exported'), 'Failed asserting that the value is not exported');
	}
	
	public function invalidValues() {
		return array(
			'nä' => array('nä'),
			'nicht doch' => array('nicht doch'), 
			'%core.debug%' => array('%core.debug%'), 
			'foo' => array('foo')
		);
	}
}

?>
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
		$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'), array());
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::SUCCESS, $result, 'Failed asserting that the validation succeeded.');
		$this->assertEquals($expectedResult, $rd->getParameter('bool'), 'Failed asserting that the validated value is the expected value');
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
		$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'), array('export' => 'exported'));
		$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value)));
		$result = $validator->execute($rd);
		$this->assertEquals(AgaviValidator::ERROR, $result, 'Failed asserting that the validation failed.');
		$this->assertNull($rd->getParameter('exported'), 'Failed asserting that the value is not exported');
		$this->assertEquals($value, $rd->getParameter('bool'), 'Failed asserting that the validated value is the original value');
	}
	
	public function invalidValues() {
		return array(
			'nä' => array('nä'),
			'nicht doch' => array('nicht doch'), 
			'%core.debug%' => array('%core.debug%'), 
			'foo' => array('foo'),
			'(int)2' => array(2),
			'(string)2' => array('2')
		);
	}
	
	public function testDontCastOnExport() {
		$testValues = array(
			array('original' => 'false', 'casted' => false),
			array('original' => 'true', 'casted' => true),
		);
		
		foreach($testValues as $value) {
			$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'), array('export' => 'exported'));
			$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value['original'])));
			$result = $validator->execute($rd);
			$this->assertEquals(AgaviValidator::SUCCESS, $result, 'Failed asserting that the validation succeeded.');
			$this->assertSame($value['casted'], $rd->getParameter('exported'), 'Failed asserting that the exported value is casted');
			$this->assertSame($value['original'], $rd->getParameter('bool'), 'Failed asserting that the validated value is untouched');
		}
	}
	
	public function testCastOnMissingExport() {
		$testValues = array(
			array('original' => 'false', 'casted' => false),
			array('original' => 'true', 'casted' => true),
		);
		
		foreach($testValues as $value) {
			$validator = $this->vm->createValidator('AgaviBooleanValidator', array('bool'), array('invalid argument'));
			$rd = new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('bool' => $value['original'])));
			$result = $validator->execute($rd);
			$this->assertEquals(AgaviValidator::SUCCESS, $result, 'Failed asserting that the validation succeeded.');
			$this->assertSame($value['casted'], $rd->getParameter('bool'), 'Failed asserting that the validated value is casted');
		}
	}
}

?>
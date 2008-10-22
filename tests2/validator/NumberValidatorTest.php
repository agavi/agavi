<?php

class NumberValidatorWrapper extends AgaviNumberValidator
{
	protected $data;

	public function setData($data)
	{
		$this->data = $data;
	}

	public function &getData($paramname)
	{
		return $this->data;
	}

	public function validate()
	{
		return parent::validate();
	}

}

class NumberValidatorTest extends AgaviTestCase
{
	/**
	 * @var AgaviValidator
	 */
	protected $validator = null;

	public function setUp()
	{
		$vm = AgaviContext::getInstance('test')->getValidationManager();
		$this->validator = new NumberValidatorWrapper($vm, array());
	}

	public function test_float()
	{
		$this->validator->clearParameters();
		$this->validator->setParameter('type', 'float');

		$good = array(
			'1',
			'-1',
			'1.0',
			'-1.0',
			'2222222222',
			'-111111',
			'-0.54',
			'-99999999.99',
		);
		$bad = array(
			'1_5',
			'BOB',
			'1.5B',
			'-1.5B',
			'%%!@#$%#',
			'9999999.-122',
		);

		foreach ($good as $value) {
			$this->validator->setData($value);
			$this->assertTrue($this->validator->validate(), "False negative: $value");
		}
		foreach ($bad as $value) {
			$this->validator->setData($value);
			$this->assertFalse($this->validator->validate(), "False positive: $value");
		}

	}

	public function test_int()
	{
		$this->validator->clearParameters();
		$this->validator->setParameter('type', 'int');

		$good = array(
			'1',
			'-1',
			'222222222',
			'-111111',
			'-54'
		);
		$bad = array(
			'1.2',
			'1_5',
			'BOB',
			'1.5B',
			'-1.5B',
			'%%!@#$%#'
		);

		foreach ($good as $value) {
			$this->validator->setData($value);
			$this->assertTrue($this->validator->validate(), "False negative: $value");
		}
		foreach ($bad as $value) {
			$this->validator->setData($value);
			$this->assertFalse($this->validator->validate(), "False positive: $value");
		}

	}

	public function test_max()
	{
		$this->validator->clearParameters();
		$this->validator->setParameter('type', 'float');
		$this->validator->setParameter('max', 4);

		$good = array(
			'3',
			'3.99999',
			'-1.24'
		);
		$bad = array(
			'5',
			'4.01',
			'10004'
		);

		foreach ($good as $value) {
			$this->validator->setData($value);
			$this->assertTrue($this->validator->validate(), "False negative: $value");
		}
		foreach ($bad as $value) {
			$this->validator->setData($value);
			$this->assertFalse($this->validator->validate(), "False positive: $value");
		}

	}

	public function test_min()
	{
		$this->validator->clearParameters();
		$this->validator->setParameter('type', 'float');
		$this->validator->setParameter('min', 4);

		$good = array(
			'5',
			'4.01',
			'4'
		);
		$bad = array(
			'3',
			'3.99999',
			'-1.24'
		);

		foreach ($good as $value) {
			$this->validator->setData($value);
			$this->assertTrue($this->validator->validate(), "False negative: $value");
		}
		foreach ($bad as $value) {
			$this->validator->setData($value);
			$this->assertFalse($this->validator->validate(), "False positive: $value");
		}

	}

}

?>
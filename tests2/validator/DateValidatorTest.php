<?php


class DateValidatorWrapper extends AgaviDateValidator
{
	protected $data;

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getData($paramname = 'param')
	{
		return $this->data;
	}

	public function validate()
	{
		return parent::validate();
	}

}

class DateValidatorTest extends AgaviTestCase
{
	/**
	 * @var AgaviValidator
	 */
	protected $validator = null;

	public function setUp()
	{
		$this->validator = new DateValidatorWrapper(new AgaviValidatorManager());
	}

	public function test_validate()
	{
		$this->validator->setParameter('check', true);

		$good = array(
			'2006-05-15',
			'06-05-15',
			'05-15',
			'15.6.2006',
			'15.06.2006',
			'05.06.2006',
			'15.06.06',
			'15 06 06',
			'15 06 2006',
			'15.06.',
			'15.6.',
			'05.06.',
			'05.',
			'05',
			'5.',
			'5',
			'06/15/2006',
			'06/05/2006',
			'6/5/2006',
			'06/15',
			'6/15',
		);

		$bad = array(
			'2006-15-15',
			'06-15-15',
			'15-15',
			'15.16.2006',
			'6.15.2006',
			'16/01/2006',
			'16/5',
			'16/01',
			'15032005',
			'This is a bad date.',
			'afternoon',
			'<b>15/3/2005</b>',
			'9999999',
		);

		foreach ($good as &$value) {
			$this->validator->setData($value);
			$this->assertTrue($this->validator->validate(), "False negative: $value");
		}
		foreach ($bad as &$value) {
			$this->validator->setData($value);
			$this->assertFalse($this->validator->validate(), "False positive: $value");
		}
	}
}
?>
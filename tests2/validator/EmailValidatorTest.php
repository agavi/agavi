<?php

class EmailValidatorWrapper extends AgaviEmailValidator
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

class EmailValidatorTest extends AgaviTestCase
{
	public function setUp()
	{
		$this->validator = new EmailValidatorWrapper(new AgaviValidatorManager());
	}

	public function tearDown()
	{
		unset($this->validator);
	}

	public function testexecute()
	{
		$good = array(
			'bob@agavi.org',
			'me.bob@agavi.org',
			'stupidmonkey@example.com',
			'anotherbunk@bunk-domain.com',
			'somethingelse@ez-bunk-domain.biz'
		);
		$bad = array(
			'bad mojo@agavi.org',
			'bunk(data)@agavi.org',
			'bunk@agavi info.com',
			'sjklsdfsfd'
		);
		$error = '';
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
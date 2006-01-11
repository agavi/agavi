<?php

class EmailValidatorTest extends UnitTestCase
{
	public function setUp()
	{
		$this->_ev = new EmailValidator();
	}

	public function testexecute()
	{
		$good = array(
			'bob@agavi.org',
			'me+bob@agavi.org',
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
			$this->assertTrue($this->_ev->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_ev->execute($value, $error), "False got true: $value");
		}
	}
}

?>
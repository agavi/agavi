<?php

class DateValidatorTest extends UnitTestCase
{
	public function setUp()
	{
		$this->_dv = new DateValidator();
		$this->_good_date = '2005-05-05';
		$this->_bad_date = 'BAD DATE';
	}

	public function testexecute()
	{
		$good = array(
			'2005-05-05',
			'2005-05-05 21:00:00',
			'March 31st, 2005',
			'5/3/2005',
			'15/3/2005',
			'Today',
			'Now',
			'Today + 1',
			'Monday',
			'Next week',
			'yesterday',
			'tomorrow',
			'pm',
			'am'
		);
		$bad = array(
		   '15032005',
			'This is a bad date.',
			'afternoon',
			'<b>15/3/2005</b>',
			'9999999'
		);
		$error = '';
		foreach ($good as &$value) {
			$this->assertTrue($this->_dv->execute($value, $error),"'$value'");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_dv->execute($value, $error),"'$value'");
		}
	}
}
?>
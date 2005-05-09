<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');
require_once('validator/DateValidator.class.php');

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
			'March 31st, 2005'
		);
		$bad = array(
			'This is a bad date.',
			'9999999'
		);
		$error = '';
		foreach ($good as &$value) {
			$this->assertTrue($this->_dv->execute($value, $error));
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_dv->execute($value, $error));
		}
	}
}

?>

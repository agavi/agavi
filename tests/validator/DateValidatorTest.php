<?php
require_once('tests/include.php');
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');
require_once('validator/DateValidator.class.php');

class DateValidatorTest extends PHPUnit2_Framework_TestCase
{
	public function setUp()
	{
		$this->_dv = new DateValidator();
		$this->_good_date = '2005-05-05';
		$this->_bad_date = 'BAD DATE';
	}

	public function testexecute_good()
	{
		$error = '';
		self::assertTrue($this->_dv->execute($this->_good_date, $error));
	}

	public function testexecute_bad()
	{
		self::assertFalse($this->_dv->execute($this->_bad_date, $error));
	}
}

?>

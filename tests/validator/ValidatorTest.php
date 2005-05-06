<?php
require_once('tests/include.php');
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');

class SampleValidator extends Validator
{
	public function execute(&$value, &$error) { }
}

class ValidatorTest extends PHPUnit2_Framework_TestCase
{
	public function setUp()
	{
		$this->_v = new SampleValidator();
	}

	public function testinitialize()
	{
		throw new PHPUnit2_Framework_IncompleteTestError;
	}

	public function testgetContext()
	{
		throw new PHPUnit2_Framework_IncompleteTestError;
	}
}

?>

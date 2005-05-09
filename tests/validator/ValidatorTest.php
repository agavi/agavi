<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');

class SampleValidator extends Validator
{
	public function execute(&$value, &$error) { }
}

class ValidatorTest extends UnitTestCase
{
	public function setUp()
	{
		$this->_v = new SampleValidator();
	}

	public function testinitialize()
	{
		$this->fail('Incomplete Test');
	}

	public function testgetContext()
	{
		$this->fail('Incomplete Test');
	}
}

?>

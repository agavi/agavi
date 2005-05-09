<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');
require_once('validator/NumberValidator.class.php');

class NumberValidatorTest extends UnitTestCase
{
	public function setUp()
	{
		$this->_nv = new NumberValidator();
	}

	public function testexecute_nan()
	{
		$this->_nv->setParameter('nan_error', 'Some other error');
		$good = array(
			'1',
			'1.0',
			'2222222222',
			'-111111',
			'-0.54'
		);
		$bad = array(
			'1_5',
			'BOB',
			'1.5B',
			'%%!@#$%#'
		);
		$error = '';
		foreach ($good as &$value) {
			$this->assertTrue($this->_nv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_nv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_max()
	{
		$bad = array(
			'5',
			'4.01',
			'10004'
		);
		$good = array(
			'3',
			'3.99999',
			'-1.24'
		);
		$this->_nv->setParameter('max', 4);
		$this->_nv->setParameter('max_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_nv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_nv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_min()
	{
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
		$this->_nv->setParameter('min', 4);
		$this->_nv->setParameter('min_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_nv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_nv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_type()
	{
		$bad = array(
			'5',
			'4',
			'-1111'
		);
		$good = array(
			'3.01',
			'3.99999',
			'-1.24'
		);
		$this->_nv->setParameter('type', 'Float');
		$this->_nv->setParameter('type_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_nv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_nv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}
}

?>

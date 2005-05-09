<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');
require_once('validator/StringValidator.class.php');

class StringValidatorTest extends UnitTestCase
{
	public function setUp()
	{
		$this->_sv = new StringValidator();
	}

	public function testexecute()
	{
		$good = array(
			'1',
			'1.0',
			'2222222222',
			'-111111',
			'-0.54',
			'1_5',
			'BOB',
			'1.5B',
			'%%!@#$%#'
		);
		$error = '';
		foreach ($good as &$value) {
			$this->assertTrue($this->_sv->execute($value, $error), "True got False: $value");
		}
	}

	public function testexecute_max()
	{
		$bad = array(
			'12345',
			'bbbbbbbb',
			'12bb34bb56bb  z',
			'      '
		);
		$good = array(
			'3',
			'3.99',
			'    '
		);
		$this->_sv->setParameter('max', 4);
		$this->_sv->setParameter('max_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_sv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_sv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_min()
	{
		$bad = array(
			'5',
			'4.',
			'  '
		);
		$good = array(
			'333',
			'3.9',
			'     '
		);
		$this->_sv->setParameter('min', 3);
		$this->_sv->setParameter('min_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_sv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_sv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_values()
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
		$this->_sv->setParameter('values', $good);
		$this->_sv->setParameter('values_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_sv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_sv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}

	public function testexecute_values_insensitive()
	{
		$bad = array(
			'nothing',
			'bunk',
			'*',
			'',
		);
		$good = array(
			'BOB',
			'BiLl',
			'Jack!'
		);
		$this->_sv->setParameter('values', array('bob', 'bill', 'jack!'));
		$this->_sv->setParameter('insensitive', true);
		$this->_sv->setParameter('values_error', 'Some other error');
		foreach ($good as &$value) {
			$this->assertTrue($this->_sv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_sv->execute($value, $error), "False got true: $value");
		}
		$this->assertEqual('Some other error', $error);
	}
}

?>

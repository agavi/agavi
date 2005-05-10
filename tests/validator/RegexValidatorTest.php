<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('validator/Validator.class.php');
require_once('validator/RegexValidator.class.php');

class RegexValidatorTest extends UnitTestCase
{
	private $_rv = null;

	public function setUp()
	{
		$this->_rv = new RegexValidator();
		// fake initialize()
		$this->_rv->setParameter('match', true);
		$this->_rv->setParameter('match_error', 'Invalid input');
		$this->_rv->setParameter('pattern', null);
	}

	public function testexecute()
	{
		$good = array(
			'nnbb',
			'nbb',
			'nnnbb'
		);
		$bad = array(
			'bb',
			'nnnnbb',
			'jdsakl'
		);
		$this->_rv->setParameter('pattern', '/^[n]{1,3}bb$/');
		$this->_rv->setParameter('match_error', 'Some other error');
		$error = '';
		foreach ($good as &$value) {
			$this->assertTrue($this->_rv->execute($value, $error), "True got False: $value");
		}
		foreach ($bad as &$value) {
			$this->assertFalse($this->_rv->execute($value, $error), "False got True: $value");
		}
		$this->assertEqual('Some other error', $error);

		$this->_rv->setParameter('match', false);
		$error = '';
		foreach ($bad as &$value) {
			$this->assertTrue($this->_rv->execute($value, $error), "True got False: $value");
		}
		foreach ($good as &$value) {
			$this->assertFalse($this->_rv->execute($value, $error), "False got True: $value");
		}
		$this->assertEqual('Some other error', $error);
	}
}

?>

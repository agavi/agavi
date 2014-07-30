<?php

require_once(__DIR__ . '/BaseValidatorTest.php');

class AgaviStringValidatorTest extends BaseValidatorTest
{
	public function testExecute()
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
			$this->doTestExecute('AgaviStringValidator', $value, AgaviValidator::SUCCESS);
		}
	}

	public function testExecuteMax()
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
		$parameters = array(
			'max' => 4,
		);
		$errors = array(
			'max' => $errorMsg = 'Some other error',
		);
		foreach ($good as &$value) {
			$this->doTestExecute('AgaviStringValidator', $value, AgaviValidator::SUCCESS, null, $errors, $parameters);
		}
		foreach ($bad as &$value) {
			$this->doTestExecute('AgaviStringValidator', $value, AgaviValidator::ERROR, $errorMsg, $errors, $parameters);
		}
	}

	public function testExecuteMin()
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
		$parameters = array(
			'min' => 3,
		);
		$errors = array(
			'min' => $errorMsg = 'Some other error',
		);
		foreach ($good as &$value) {
			$this->doTestExecute('AgaviStringValidator', $value, AgaviValidator::SUCCESS, null, $errors, $parameters);
		}
		foreach ($bad as &$value) {
			$this->doTestExecute('AgaviStringValidator', $value, AgaviValidator::ERROR, $errorMsg, $errors, $parameters);
		}
	}
}

?>
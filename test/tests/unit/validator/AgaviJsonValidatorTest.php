<?php

require_once(__DIR__ . '/BaseValidatorTest.php');

class AgaviJsonValidatorTest extends BaseValidatorTest
{
	public function testExecute()
	{
		$this->doTestExecute('AgaviJsonValidator', json_encode(array('foo' => 'bar')), AgaviValidator::SUCCESS);
		
		$errors = array(
			'syntax' => $errorMsg = 'Syntax error',
		);
		$this->doTestExecute('AgaviJsonValidator', '{', AgaviValidator::ERROR, $errorMsg, $errors);
	}

	public function testExport()
	{
		$value = array('foo' => 'bar');

		$res = $this->executeValidator('AgaviJsonValidator', json_encode($value), array(), array(
			'export' => 'test',
		));
		$this->assertEquals($res['rd']->getParameter('test'), $value);

		$res = $this->executeValidator('AgaviJsonValidator', json_encode($value), array(), array(
			'export' => 'test',
			'assoc'  => false,
		));
		$this->assertEquals($res['rd']->getParameter('test'), (object)$value);
	}
}

?>
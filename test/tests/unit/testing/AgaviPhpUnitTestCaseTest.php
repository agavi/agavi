<?php

class AgaviPhpUnitTestCaseTest extends AgaviPhpUnitTestCase
{
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->setRunTestInSeparateProcess(true);
		$this->setIsolationEnvironment('testing.testIsolation');
	}
	
	public function testIsolationEnvironment()
	{
		$this->assertEquals('testing.testIsolation', AgaviConfig::get('testing.environment'));
	}
}

?>
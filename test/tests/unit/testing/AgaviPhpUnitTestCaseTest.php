<?php
/**
 * @runTestsInSeparateProcesses
 * @agaviIsolationEnvironment testing.testIsolation	
 * @agaviIsolationDefaultContext web-isolated
 */
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
		$this->setIsolationEnvironment('testing.testIsolation'); // equivalent to the annotation @AgaviIsolationEnvironment on the testcase class
	}
	
	public function testIsolationEnvironment()
	{
		$this->assertEquals('testing.testIsolation', AgaviConfig::get('testing.environment'));
	}
	
	/**
	 * @agaviIsolationEnvironment testing.testIsolationAnnotated
	 */
	public function testIsolationEnvironmentAnnotated()
	{
		$this->assertEquals('testing.testIsolationAnnotated', AgaviConfig::get('testing.environment'));
	}
	
	public function testIsolationDefaultContext()
	{
		$this->assertEquals('web-isolated', AgaviConfig::get('core.default_context'));
	}
	
	/**
	 * @agaviIsolationDefaultContext web-isolated-annotated-method
	 */
	public function testIsolationDefaultContextAnnotated()
	{
		$this->assertEquals('web-isolated-annotated-method', AgaviConfig::get('core.default_context'));
	}
}

?>
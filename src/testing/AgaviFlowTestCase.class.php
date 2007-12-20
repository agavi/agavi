<?php

class AgaviFlowTestCase extends PHPUnit_Framework_TestCase implements AgaviIFlowTestCase
{
	/**
	 * @var        bool Indicates to PHPUnit whether or not each test method in this test case should run in isolation. In this case, we want each method of the test case to run isolated in a separate process.
	 */
	protected $runTestsInSeparateProcesses = true;
	
	protected function setUp()
	{
		// this method will be called in each of the child processes used to run the individual test methods in isolation
		// those, of course, need agavi
		
		// load agavi
		require_once(realpath(dirname(__FILE__) . '/../agavi.php'));
		
		// TODO: carry over env name to bootstrap from parent process via passed $GLOBALS
		Agavi::bootstrap('testing-david');
		
		// TODO: grab a context?
	}
}

?>
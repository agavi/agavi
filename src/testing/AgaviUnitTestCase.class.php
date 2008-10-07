<?php

class AgaviUnitTestCase extends PHPUnit_Framework_TestCase implements AgaviIUnitTestCase
{
	/**
	 * @var        bool Indicates to PHPUnit whether or not each test case in this suite should run in isolation. In this case, we want all methods of the test case to run in the same process, but the entire test case run in the suite should be isolated from others, so this must be set to true.
	 */
	protected $runInSeparateProcess = true;
	
	public function __construct()
	{
		// TODO: carry over env name to bootstrap from parent process via passed $GLOBALS
		Agavi::bootstrap('testing-david');
	}
	
	protected function setUp()
	{
		// all methods of this test case run in the same process
	}
}

?>
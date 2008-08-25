<?php

abstract class AgaviFragmentTestCase implements AgaviIFragmentTestCase
{
	/**
	 * @var        bool Indicates to PHPUnit whether or not each test method in this test case should run in isolation. In this case, we want each method of the test case to run isolated in a separate process.
	 */
	protected $runTestsInSeparateProcesses = true;
		
	public function getContext()
	{
		/**
		 * @TODO change this implementation, it's only here for dev purposes
		 */
		return AgaviContext::getInstance('web');
	}
}

?>
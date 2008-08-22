<?php

class AgaviUnitTestSuite extends AgaviTestSuite
{
	
	/**
     * Whether or not the tests of this test suite are
     * to be run in separate PHP processes.
     *
     * @var    boolean
     */
    protected $runTestsInSeparateProcesses = true;

    /**
     * Whether or not the tests of this test suite are
     * to be run in a separate PHP process.
     *
     * @var    boolean
     */
    protected $runTestSuiteInSeparateProcess = true;
	
	public static function suite()
	{
		$c = new self();
		
		return $c;
	}
	
	protected function setUp()
	{
		// this method will be called in each of the child processes used to run the individual test cases in isolation
		// those, of course, need agavi
		
		// TODO: grab a context?
	}
}

?>
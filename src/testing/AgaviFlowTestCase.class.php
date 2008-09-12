<?php

abstract class AgaviFlowTestCase extends PHPUnit_Framework_TestCase implements AgaviIFlowTestCase
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
	}
	
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
<?php

class AgaviUnitTestSuite extends AgaviTestSuite
{
	public static function suite()
	{
		$c = new self();
		
		return $c;
	}
	
	protected function setUp()
	{
		// this method will be called in each of the child processes used to run the individual test cases in isolation
		// those, of course, need agavi
		
		// load agavi
		require_once(realpath(dirname(__FILE__) . '/../agavi.php'));
		
		// TODO: carry over env name to bootstrap from parent process via passed $GLOBALS
		Agavi::bootstrap('testing-david');
		
		// TODO: grab a context?
	}
}

?>
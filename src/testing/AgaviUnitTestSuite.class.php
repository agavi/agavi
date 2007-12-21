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
		
		// TODO: grab a context?
	}
}

?>
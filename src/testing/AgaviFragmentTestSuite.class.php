<?php

class AgaviFragmentTestSuite extends AgaviTestSuite
{
	public static function suite()
	{
		$c = new self();
		
		return $c;
	}
	
	public function setUp()
	{
		// all methods of this test case run in the same process
	}
}

?>
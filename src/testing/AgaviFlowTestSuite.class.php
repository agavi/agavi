<?php

class AgaviFlowTestSuite extends AgaviTestSuite
{
	public static function suite()
	{
		$c = __CLASS__;
		$c = new $c();
		
		return $c;
	}
	
	protected function setUp()
	{
		// all cases of this suite run in the same process
	}
}

?>
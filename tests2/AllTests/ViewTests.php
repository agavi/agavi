<?php
require_once(dirname(__FILE__) . '/../view/ViewTest.php');

class ViewTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('view');

		$suite->addTestSuite('ViewTest');
		return $suite;
	}
}

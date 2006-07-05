<?
require_once(dirname(__FILE__) . '/../util/AttributeHolderTest.php');
require_once(dirname(__FILE__) . '/../util/ParameterHolderTest.php');
require_once(dirname(__FILE__) . '/../util/ToolkitTest.php');

class UtilTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('util');

		$suite->addTestSuite('AttributeHolderTest');
		$suite->addTestSuite('ParameterHolderTest');
		$suite->addTestSuite('ToolkitTest');

		return $suite;
	}
}

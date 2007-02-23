<?
require_once(dirname(__FILE__) . '/../util/AttributeHolderTest.php');
require_once(dirname(__FILE__) . '/../util/ParameterHolderTest.php');
require_once(dirname(__FILE__) . '/../util/ToolkitTest.php');
require_once(dirname(__FILE__) . '/../util/DecimalFormatterTest.php');
//require_once(dirname(__FILE__) . '/../util/PathTest.php');

class UtilTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('util');

		$suite->addTestSuite('AttributeHolderTest');
		$suite->addTestSuite('ParameterHolderTest');
		$suite->addTestSuite('ToolkitTest');
		$suite->addTestSuite('DecimalFormatterTest');
//		$suite->addTestSuite('PathTest');

		return $suite;
	}
}

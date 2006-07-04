<?
require_once(dirname(__FILE__) . '/../renderer/RendererTest.php');

class RendererTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('renderer');

		$suite->addTestSuite('RendererTest');

		return $suite;
	}
}

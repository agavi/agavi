<?php
require_once(dirname(__FILE__) . '/../routing/RoutingTest.php');

require_once(dirname(__FILE__) . '/../routing/WebRoutingTest.php');

class RoutingTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('routing');

		$suite->addTestSuite('RoutingTest');

		$webSuite = new PHPUnit_Framework_TestSuite('WebRouting');
		$d = dir(dirname(__FILE__) . '/../routing/cases/');
		while(false !== ($entry = $d->read())) {
			if(preg_match('#.*\\.case\\.php#i', $entry))
			{
				$cases = include($d->path . $entry);
				foreach($cases as $case) {
					$tc = new WebRoutingTest();
					$tc->setName(str_replace('.', '_', $entry));
					$tc->setExport($case);
					//$suite->addTestSuite(new ReflectionClass($tc));
					$suite->addTest($tc);
				}
			}
		}
		$d->close();

		//$suite->addTest($webSuite);
		return $suite;
	}
}

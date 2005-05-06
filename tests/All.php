<?php
require_once('PHPUnit2/Framework/TestCase.php');
set_include_path(get_include_path().':'.dirname(dirname(__file__)).':'.dirname(dirname(__file__)).'/src');

class All {
	public function __construct($s)
	{
		parent::__construct($s);
	}

	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite();
		foreach (glob(dirname(__file__).'/*/*Test*.php') as $file) {
			require_once($file);
			$className = basename($file, '.php');
			$suite->addTest(new PHPUnit2_Framework_TestSuite($className));
		}
		return $suite;
	}
}

?>
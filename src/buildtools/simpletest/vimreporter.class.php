<?php
/**
 * Vim Reporter
 * 
 * Basicly just a simplified, condensed text reporter, 
 * certainly not pretty but more usefully formatted. :)
 *
 * @package Agavi
 * @subpackage UnitTester
 * @version 0.0.1 2005/06/23
 * @author Mike Vincent (mike@agavi.org)
 */

class VIMReporter extends TextReporter
{
	public function __construct()
	{
		$this->TextReporter();
	}

 	public function paintHeader($test_name)
	{
	}

	public function paintFooter($test_name)
	{
		echo  'Test cases run: ' . $this->getTestCaseProgress() .
					'/' . $this->getTestCaseCount() .
					', Passes: ' . $this->getPassCount() .
					', Failures: ' . $this->getFailCount() .
					', Exceptions: ' . $this->getExceptionCount() ."\n";
	}

/* when we do a -Dfrom=model, we dont get the group line...
 * 1) True assertion got False at line [51]
 *         in testGenerate
 *         in PropelFormTest
 *         in /var/www/sites/agavi-trunk/tests/model/PropelFormTest.php
 */

/* 1) True assertion got False at line [51]
 *        in testGenerate
 *        in PropelFormTest
 *        in /var/www/sites/agavi-trunk/tests/model/PropelFormTest.php
 *        in Tests Test Suite
 */
	public function paintFail($message)
	{
		$this->_fails++;
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		if (count($breadcrumb) > 3) {
			list($group, $file, $class, $method) = $breadcrumb;
		} else {
			list($file, $class, $method) = $breadcrumb;
		}
		preg_match('/^(.*)at\ line\ \[(\d+)\]$/', $message, $matches);	
		echo  'Failure #'. $this->getFailCount() .') '.
					"Line: #{$matches[2]} File: $file " .
					"Msg: {$matches[1]}\n";
	}
}
?>

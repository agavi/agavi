<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * Vim Reporter
 * 
 * Basicly just a simplified, condensed text reporter, 
 * certainly not pretty but more usefully formatted. :)
 *
 * @package    agavi
 * @subpackage UnitTester
 *
 * @author     Mike Vincent <mike@agavi.org>
 *
 * @version    $Id$
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
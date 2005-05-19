#!/usr/bin/env php
<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');
set_include_path(get_include_path().':'.dirname(dirname(__file__)).'/src');

$opts = getopt('t:m:');
$opts['m'] = (empty($opts['m']) ? '*' : $opts['m']);
$opts['t'] = (empty($opts['t']) ? 'Text' : $opts['t']);

$test = new GroupTest('Agavi Test Suite');
foreach (glob(dirname(__file__)."/{$opts['m']}") as $dir) {
	if (!is_dir($dir)) { continue; }
	$group = &new GroupTest(dirname($dir) . ' Test Suite');
	foreach (glob("{$dir}/*Test*.php") as $file) {
		$group->addTestFile($file);
	}
	$test->addTestCase($group);
}
if (strtolower($opts['t']) == 'html')
	$rclass = 'HTMLReporter';
else
	$rclass = 'TextReporter';
$test->run(new $rclass());

?>

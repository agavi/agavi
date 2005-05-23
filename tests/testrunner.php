#!/usr/bin/env php
<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');
set_include_path(get_include_path().':'.dirname(dirname(__file__)).'/src');

$opts = getopt('t:m:');
// had to temporarily default to the controller tests because something elsewhere seems to be mucking with my environment
// $opts['m'] = (empty($opts['m']) ? '*' : $opts['m']);
$opts['m'] = (empty($opts['m']) ? 'controller' : $opts['m']);
$opts['t'] = (empty($opts['t']) ? 'Text' : $opts['t']);

$test = new GroupTest('Agavi Test Suite');
foreach (glob(dirname(__file__)."/{$opts['m']}") as $dir) {
	//echo 'Checking for ' . basename($dir) .' tests: ';
	if (!is_dir($dir)) { continue; }
	$group = &new GroupTest(basename($dir) . ' Test Suite');
	foreach (glob("{$dir}/*Test*.php") as $file) {
		//echo basename($file).' ';
		$group->addTestFile($file);
	}
	//echo "done\n";
	$test->addTestCase($group);
}
if (strtolower($opts['t']) == 'html')
	$rclass = 'HTMLReporter';
else
	$rclass = 'TextReporter';
$test->run(new $rclass());
?>

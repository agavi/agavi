<?php

// copied from PHPUnit/Util/Process/TestMethod.tpl
set_include_path('{include_path}');
$GLOBALS = unserialize({globals});
// end copied from PHPUnit/Util/Process/TestMethod.tpl

require_once('testing.php');

// copied from PHPUnit/Util/Process/TestMethod.tpl
require_once 'PHPUnit/Framework.php';
require_once '{filename}';

$result = new PHPUnit_Framework_TestResult;
$result->collectCodeCoverageInformation({collectCodeCoverageInformation});

$test = new {className}('{methodName}', {data}, '{dataName}');
$test->run($result);

print serialize($result);
// end copied from PHPUnit/Util/Process/TestMethod.tpl

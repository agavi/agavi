<?php

// copied from PHPUnit/Util/Process/TestMethod.tpl
set_include_path('{include_path}');
$GLOBALS = unserialize({globals});
$GLOBALS['GLOBALS'] = &$GLOBALS;
// end copied from PHPUnit/Util/Process/TestMethod.tpl

// bootstrap an agavi installation, so tests can run without problems
require_once('testing.php');
AgaviConfig::fromArray($GLOBALS['_ENV']['AGAVI']);
unset($GLOBALS['_ENV']['AGAVI']);

AgaviTesting::bootstrap();

// copied from PHPUnit/Util/Process/TestMethod.tpl
require_once 'PHPUnit/Framework.php';
require_once '{filename}';

$result = new PHPUnit_Framework_TestResult;
$result->collectCodeCoverageInformation({collectCodeCoverageInformation});

$test = new {className}('{methodName}', {data}, '{dataName}');
$test->run($result);

print serialize($result);
// end copied from PHPUnit/Util/Process/TestMethod.tpl

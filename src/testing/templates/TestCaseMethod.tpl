<?php
function __phpunit_run_isolated_test()
{
    $result = new PHPUnit_Framework_TestResult;
    $result->collectRawCodeCoverageInformation({collectCodeCoverageInformation});

    $test = new {className}('{methodName}', unserialize('{data}'), '{dataName}');
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setInIsolation(TRUE);
    $test->run($result);

    print serialize(
      array(
        'testResult'    => $test->getResult(),
        'numAssertions' => $test->getNumAssertions(),
        'result'        => $result
      )
    );
}

{globals}
set_include_path('{include_path}');

require_once('testing.php');
AgaviConfig::fromArray($GLOBALS['AGAVI_CONFIG']);
unset($GLOBALS['AGAVI_CONFIG']);

if({agavi_clear_cache}) {
	AgaviToolkit::clearCache();
}

$env = null;

if('' != '{agavi_environment}') {
	$env = '{agavi_environment}';
}

AgaviTesting::bootstrap($env);

if('' != '{agavi_default_context}') {
	AgaviConfig::set('core.default_context', '{agavi_default_context}');
}

require_once 'PHPUnit/Framework.php';
require_once '{filename}';


__phpunit_run_isolated_test()
?>

<?php

// ***
// This file is based on https://github.com/sebastianbergmann/phpunit/blob/3.5.15/PHPUnit/Framework/Process/TestCaseMethod.tpl.dist and https://github.com/sebastianbergmann/phpunit/blob/3.6.4/PHPUnit/Framework/Process/TestCaseMethod.tpl.dist with some lines commented out and a version switch inside __phpunit_run_isolated_test() to cater for the different code coverage collection mechanisms between versions
// ***

set_include_path('{include_path}');
// removal reason: testing.php includes that one
// require_once 'PHPUnit/Autoload.php';
ob_start();

function __phpunit_run_isolated_test()
{
    if (!class_exists('{className}')) {
        require_once '{filename}';
    }

    $result = new PHPUnit_Framework_TestResult;

    if(version_compare(PHPUnit_Runner_Version::id(), '3.6', '<')) { // testing.php includes Version.php
        $result->collectRawCodeCoverageInformation({collectCodeCoverageInformation});
    } else {
        if ({collectCodeCoverageInformation}) {
            $result->setCodeCoverage(new PHP_CodeCoverage);
        }
    }

    $result->strictMode({strict});

    $test = new {className}('{methodName}', unserialize('{data}'), '{dataName}');
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setInIsolation(TRUE);

    ob_end_clean();
    ob_start();
    $test->run($result);
    $output = ob_get_clean();

    print serialize(
      array(
        'testResult'    => $test->getResult(),
        'numAssertions' => $test->getNumAssertions(),
        'result'        => $result,
        'output'        => $output
      )
    );

    ob_start();
}

{constants}
// removal reason: will screw up order of inclusions, we have autoloaders for this
/*{included_files}*/
{globals}

// *** BEGIN CUSTOM AGAVI CODE ***
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
// *** END CUSTOM AGAVI CODE ***

// removal reason: will screw up order of inclusions, we have autoloaders for this
// if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
//     require_once $GLOBALS['__PHPUNIT_BOOTSTRAP'];
//     unset($GLOBALS['__PHPUNIT_BOOTSTRAP']);
// }

__phpunit_run_isolated_test();
ob_end_clean();
?>
<?php

$agaviTestSettings = $GLOBALS['AGAVI_TESTING_ISOLATED_TEST_SETTINGS'];
unset($GLOBALS['AGAVI_TESTING_ISOLATED_TEST_SETTINGS']);

if($agaviTestSettings['bootstrap'] || $agaviTestSettings['clearCache']) {
	require(__DIR__ . '/../../testing.php');
}

if($agaviTestSettings['bootstrap']) {
	// when agavi is not bootstrapped we don't want / need to load the agavi config
	// values from outside the isolation
	AgaviConfig::fromArray($GLOBALS['AGAVI_TESTING_CONFIG']);
}
unset($GLOBALS['AGAVI_TESTING_CONFIG']);

if($agaviTestSettings['clearCache']) {
	AgaviToolkit::clearCache();
}

$env = null;

if($agaviTestSettings['environment']) {
	$env = $agaviTestSettings['environment'];
}

if($agaviTestSettings['bootstrap']) {
	AgaviTesting::bootstrap($env);
}

if($agaviTestSettings['defaultContext']) {
	AgaviConfig::set('core.default_context', $agaviTestSettings['defaultContext']);
}

if(!defined('AGAVI_TESTING_BOOTSTRAPPED')) {
	// when PHPUnit runs with preserve global state enabled, AGAVI_TESTING_BOOTSTRAPPED will already be defined
	define('AGAVI_TESTING_BOOTSTRAPPED', true);
}

if(AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP) {
	require_once(AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP);
}


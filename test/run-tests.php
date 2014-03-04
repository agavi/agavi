<?php

$composer_autoload = dirname(__FILE__).'/../vendor/autoload.php';

if(is_readable($composer_autoload)) {
	require($composer_autoload);
}

require('../src/testing.php');

require('config.php');

$arguments = AgaviTesting::processCommandlineOptions(); 

if(isset($arguments['environment'])) {
	$env = $arguments['environment'];
	unset($arguments['environment']);
} else {
	$env = 'testing';
}

AgaviToolkit::clearCache();

AgaviTesting::bootstrap($env);

AgaviTesting::getCodeCoverageFilter()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));

AgaviTesting::dispatch($arguments);

?>

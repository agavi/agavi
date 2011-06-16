<?php

require('../src/testing.php');

require('config.php');

$arguments = AgaviTesting::processCommandlineOptions(); 

if(isset($arguments['environment'])) {
	$env = $arguments['environment'];
	unset($arguments['environment']);
} else {
	$env = 'testing';
}

AgaviTesting::bootstrap($env);

PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist(AgaviConfig::get('core.cache_dir'));

AgaviTesting::dispatch($arguments);

?>
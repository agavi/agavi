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

PHPUnit_Util_Filter::addDirectoryToFilter(AgaviConfig::get('core.cache_dir'));

AgaviTesting::dispatch($arguments);

?>
<?php

$here = realpath(dirname(__FILE__));

// load Agavi basics
require_once($here . '/agavi.php');

// check minimum PHP version
AgaviConfig::set('core.minimum_php_version', '5.3.0-dev');
if(!version_compare(PHP_VERSION, AgaviConfig::get('core.minimum_php_version'), 'ge') ) {
	die('You must be using PHP version ' . AgaviConfig::get('core.minimum_php_version') . ' or greater.');
}

// AgaviTesting class
require_once($here . '/testing/AgaviTesting.class.php');

// add our bundled PHPUnit to include path (until a new release is out :D)
set_include_path($here . '/vendor' . PATH_SEPARATOR . get_include_path());

// load PHPUnit basics
require_once('PHPUnit/TextUI/TestRunner.php');

?>
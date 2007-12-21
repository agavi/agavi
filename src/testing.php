<?php

$here = realpath(dirname(__FILE__));

// load Agavi basics
require_once($here . '/agavi.php');

// AgaviTesting class
require_once($here . '/testing/AgaviTesting.class.php');

// add our bundled PHPUnit to include path (until a new release is out :D)
set_include_path($here . '/vendor' . PATH_SEPARATOR . get_include_path());

// load PHPUnit basics
require_once('PHPUnit/TextUI/TestRunner.php');

?>
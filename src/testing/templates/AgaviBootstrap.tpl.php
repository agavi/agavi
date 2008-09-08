<?php
require_once('testing.php');
AgaviConfig::fromArray($GLOBALS['AGAVI_CONFIG']);
unset($GLOBALS['AGAVI_CONFIG']);

AgaviTesting::bootstrap();
?>
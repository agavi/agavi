<?php
error_reporting(E_ALL); // simpletest isnt STRICT compliant :(

// set this to location you want to write the cache of class locations to enable caching (eg: dirname(__FILE__), make sure it's writable )
if (!defined('AG_TEST_CACHE_DIR')) {
	define('AG_TEST_CACHE_DIR', false); 
}

// Define some path constants for our sandboxed test environment
define('AG_WEBAPP_DIR',	dirname(__FILE__) . '/sandbox');
define('AG_CONFIG_DIR',	AG_WEBAPP_DIR . '/config');
define('AG_CACHE_DIR',	AG_WEBAPP_DIR . '/cache');
define('AG_LIB_DIR',		AG_WEBAPP_DIR . '/lib');
define('AG_MODULE_DIR',	AG_WEBAPP_DIR . '/modules/');

define('AG_PATH_INFO_ARRAY', 'SERVER');
define('AG_PATH_INFO_KEY', 'PATH_INFO');
define('AG_AVAILABLE', 'On');
define('AG_USE_DATABASE', true);
define('AG_USE_SECURITY', true);
define('AG_USE_LOGGING', true);
define('AG_CONTENT_TYPE', 'html');
define('AG_REQUEST_NAMESPACE', 'org/agavi');

// define('AG_ERROR_404_MODULE', 'ErrorModule');
// define('AG_ERROR_404_ACTION', 'Error404');
// define('AG_MODULE_DISABLED_MODULE', 'ErrorModule');
// define('AG_MODULE_DISABLED_ACTION', 'ModuleUnavailable');
// define('AG_MAX_FORWARDS', 3);


?>
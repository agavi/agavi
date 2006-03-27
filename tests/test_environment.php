<?php
error_reporting(E_ALL); // simpletest isnt STRICT compliant :(

// set this to location you want to write the cache of class locations to enable caching (eg: dirname(__FILE__), make sure it's writable )
AgaviConfig::set('tests.cache_dir', false, false);

// Define some path constants for our sandboxed test environment
AgaviConfig::set('core.webapp_dir', dirname(__FILE__) . '/sandbox');
AgaviConfig::set('core.config_dir', AgaviConfig::get('core.webapp_dir') . '/config');
AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.webapp_dir') . '/cache');
AgaviConfig::set('core.lib_dir', AgaviConfig::get('core.webapp_dir') . '/lib');
AgaviConfig::set('core.module_dir', AgaviConfig::get('core.webapp_dir') . '/modules/');

AgaviConfig::set('request.path_info_array', 'SERVER');
AgaviConfig::set('request.path_info_key', 'PATH_INFO');
AgaviConfig::set('core.available', 'On');
AgaviConfig::set('core.use_database', true);
AgaviConfig::set('core.use_security', true);
AgaviConfig::set('core.use_logging', true);
AgaviConfig::set('controller.content_type', 'text/html');
AgaviConfig::set('user.default_namespace', 'org.agavi');
AgaviConfig::set('user.request_namespace', 'org.agavi');
AgaviConfig::set('request.url_format', 'GET');


// define('AG_ERROR_404_MODULE', 'ErrorModule');
// define('AG_ERROR_404_ACTION', 'Error404');
// define('AG_MODULE_DISABLED_MODULE', 'ErrorModule');
// define('AG_MODULE_DISABLED_ACTION', 'ModuleUnavailable');
// define('AG_MAX_FORWARDS', 3);


?>
<?php

/**
 * If PHP has no timezone set, use London (UTC) as a fallback.
 * Otherwise the tests would fail unnecessarily.
 */
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'Europe/London');
}

AgaviConfig::set('core.testing_dir', realpath(__DIR__));
AgaviConfig::set('core.app_dir', realpath(__DIR__.'/sandbox/app/'));
AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.app_dir') . '/cache'); // for the clearCache() before bootstrap()

?>

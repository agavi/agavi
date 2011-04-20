<?php

AgaviConfig::set('core.testing_dir', realpath(dirname(__FILE__)));
AgaviConfig::set('core.app_dir', realpath(dirname(__FILE__).'/../app/'));
AgaviConfig::set('core.cache_dir', AgaviConfig::get('core.app_dir') . '/cache'); // for the clearCache() before bootstrap()

?>
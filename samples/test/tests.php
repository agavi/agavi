<?php

require('../../src/testing.php');

require('config.php');

AgaviTesting::bootstrap('testing-felix');

PHPUnit_Util_Filter::addDirectoryToFilter(AgaviConfig::get('core.cache_dir'));
PHPUnit_Util_Filter::addDirectoryToFilter(AgaviConfig::get('core.agavi_dir'));

AgaviTesting::dispatch();

?>
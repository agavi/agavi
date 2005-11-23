<?php

// +---------------------------------------------------------------------------+
// | Should we run the system in debug mode? When this is on, there may be     |
// | various side-effects. But for the time being it only deletes the cache    |
// | upon start-up.                                                            |
// |                                                                           |
// | This should stay on while you're developing your application, because     |
// | many errors can stem from the fact that you're using an old cache file.   |
// |                                                                           |
// | This constant will be auto-set by Agavi if you do not supply it.          |
// | The default value is: <false>                                             |
// +---------------------------------------------------------------------------+
// define('AG_DEBUG', true);

// +---------------------------------------------------------------------------+
// | The PHP error reporting level.                                            |
// |                                                                           |
// | See: http://www.php.net/error_reporting                                   |
// |                                                                           |
// | This constant will be auto-set by Agavi if you do not supply it.          |
// | The default value is: <E_ALL | E_STRICT>                                  |
// +---------------------------------------------------------------------------+
// define('AG_ERROR_REPORTING', E_ALL | E_STRICT);

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi package. This directory          |
// | contains all the Agavi packages.                                          |
// |                                                                           |
// | This constant will be auto-set by Agavi if you do not supply it.          |
// | The default value is the name of the directory "agavi.php" resides in.    |
// +---------------------------------------------------------------------------+
// define('AG_APP_DIR', '%%PATH_TO_AGAVI%%');

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to your web application directory. This       |
// | directory is the root of your web application, which includes the core    |
// | configuration files and related web application data.                     |
// | You shouldn't have to change this usually since it's auto-determined.     |
// | Agavi can't determine this automatically, so you always have to supply it.|
// +---------------------------------------------------------------------------+
define('AG_WEBAPP_DIR', dirname(__FILE__));

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the directory where cache files will be    |
// | stored.                                                                   |
// |                                                                           |
// | NOTE: If you're going to use a public temp directory, make sure this is a |
// |       sub-directory of the temp directory. The cache system will attempt  |
// |       to clean up *ALL* data in this directory.                           |
// |                                                                           |
// | This constant will be auto-set by Agavi if you do not supply it.          |
// | The default value is: <AG_WEBAPP_DIR . '/cache'>                          |
// +---------------------------------------------------------------------------+
// define('AG_CACHE_DIR', AG_WEBAPP_DIR . '/cache');

// +---------------------------------------------------------------------------+
// | You may also modify the following other constants in this file:           |
// |  - AG_CONFIG_DIR   (defaults to <AG_WEBAPP_DIR . '/config'>)              |
// |  - AG_LIB_DIR      (defaults to <AG_WEBAPP_DIR . '/lib'>)                 |
// |  - AG_MODULE_DIR   (defaults to <AG_WEBAPP_DIR . '/modules'>)             |
// |  - AG_TEMPLATE_DIR (defaults to <AG_WEBAPP_DIR . '/templates'>)           |
// +---------------------------------------------------------------------------+

?>
<?php

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/agavi.php script.                |
// +---------------------------------------------------------------------------+
require_once('INSERT PATH TO "agavi/agavi.php" HERE');

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our webapp/config.php script.              |
// +---------------------------------------------------------------------------+
require_once('../webapp/config.php');

// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method |
// +---------------------------------------------------------------------------+
Agavi::bootstrap();

// +---------------------------------------------------------------------------+
// | Call the controller's dispatch method on the default context              |
// +---------------------------------------------------------------------------+
AgaviContext::getInstance()->getController()->dispatch();
?>
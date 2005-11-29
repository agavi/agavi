<?php

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our webapp/config.php script.              |
// +---------------------------------------------------------------------------+
require_once('INSERT PATH TO "webapp/config.php" HERE');

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/agavi.php script.              |
// +---------------------------------------------------------------------------+
require_once('INSERT PATH TO "agavi/agavi.php" HERE');

// +---------------------------------------------------------------------------+
// | Call the controller's dispatch method on the default context              |
// +---------------------------------------------------------------------------+
Context::getInstance()->getController()->dispatch();
?>

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
// | Create our controller. For this file we're going to use a front           |
// | controller pattern. This pattern allows us to specify module and action   |
// | GET/POST parameters and it automatically detects them and finds the       |
// | expected action.                                                          |
// +---------------------------------------------------------------------------+
$controller = Controller::newInstance('FrontWebController');

// +---------------------------------------------------------------------------+
// | Dispatch our request.                                                     |
// +---------------------------------------------------------------------------+
$controller->dispatch();

?>

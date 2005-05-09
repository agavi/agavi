<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * Version initialization script.
 *
 * @package agavi
 * 
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     3.0.0
 * @version   $Id$
 */

define('MO_APP_NAME',          'Agavi');

define('MO_APP_MAJOR_VERSION', '3');

define('MO_APP_MINOR_VERSION', '0');

define('MO_APP_MICRO_VERSION', '0');

define('MO_APP_BRANCH',        'dev-3.0.0');

define('MO_APP_STATUS',        'DEV');

define('MO_APP_VERSION',       MO_APP_MAJOR_VERSION . '.' .
						       MO_APP_MINOR_VERSION . '.' .
						       MO_APP_MICRO_VERSION . '-' . MO_APP_STATUS);

define('MO_APP_URL',           'http://www.agavi.org');

define('MO_APP_INFO',          MO_APP_NAME . ' ' . MO_APP_VERSION .
						       ' (' . MO_APP_URL . ')');

?>

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * Version initialization script.
 *
 * @package agavi
 * 
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */

define('AG_APP_NAME',          'Agavi');

define('AG_APP_MAJOR_VERSION', '0');

define('AG_APP_MINOR_VERSION', '10');

define('AG_APP_MICRO_VERSION', '0');

define('AG_APP_BRANCH',        'bob');

define('AG_APP_STATUS',        'DEV');

define('AG_APP_VERSION',       AG_APP_MAJOR_VERSION . '.' .
						       AG_APP_MINOR_VERSION . '.' .
						       AG_APP_MICRO_VERSION . '-' . AG_APP_STATUS);

define('AG_APP_URL',           'http://www.agavi.org');

define('AG_APP_INFO',          AG_APP_NAME . ' ' . AG_APP_VERSION .
						       ' (' . AG_APP_URL . ')');

?>

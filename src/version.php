<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * @package    agavi
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */

AgaviConfig::set('agavi.name', 'Agavi');

AgaviConfig::set('agavi.major_version', '1');
AgaviConfig::set('agavi.minor_version', '0');
AgaviConfig::set('agavi.micro_version', '7');
// AgaviConfig::set('agavi.status', '');
AgaviConfig::set('agavi.branch', '1.0');

AgaviConfig::set('agavi.version',
	AgaviConfig::get('agavi.major_version') . '.' .
	AgaviConfig::get('agavi.minor_version') . '.' .
	AgaviConfig::get('agavi.micro_version') .
	(AgaviConfig::has('agavi.status')
		? '-' . AgaviConfig::get('agavi.status')
		: '')
);

AgaviConfig::set('agavi.release',
	AgaviConfig::get('agavi.name') . '/' .
	AgaviConfig::get('agavi.version')
);

AgaviConfig::set('agavi.url', 'http://www.agavi.org');

AgaviConfig::set('agavi_info',
	AgaviConfig::get('agavi.release') . ' (' .
	AgaviConfig::get('agavi.url') . ')'
);

?>
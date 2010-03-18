<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * AgaviPassthruLoggerLayout is an AgaviLoggerLayout that will return the 
 * AgaviLoggerMessage text unaltered.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviPassthruLoggerLayout extends AgaviLoggerLayout
{
	/**
	 * Format a message.
	 *
	 * @param      AgaviLoggerMessage An AgaviLoggerMessage instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function format(AgaviLoggerMessage $message)
	{
		return $message->__toString();
	}
}

?>
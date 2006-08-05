<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project                                 |
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
 * AgaviPassthruLayout is an AgaviLayout that will return the AgaviMessage text 
 * unaltered.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviPassthruLayout extends AgaviLayout
{
	/**
	 * Format a message.
	 *
	 * @param      AgaviMessage A AgaviMessage instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function format($message)
	{
		return $message->__toString();
	}
}

?>
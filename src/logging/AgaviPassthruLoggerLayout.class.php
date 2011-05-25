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
 * AgaviPassthruLoggerLayout is an AgaviLoggerLayout that will return the entire
 * AgaviLoggerMessage or parts of it, depending on the configuration.
 * 
 * Parameter "mode" controls the four possible modes of operation:
 *   'to_string' - return AgaviLoggerMessage::__toString() (default)
 *   'full'      - return the full AgaviLoggerMessage object
 *   'message'   - return AgaviLoggerMessage::getMessage()
 *   'parameter' - return only one parameter of the object. By default, this is
 *                 "message"; can be changed using parameter "parameter".
 * Parameter "parameter" controls which parameter of the AgaviLoggerMessage
 * object is used when "mode" is "parameter". Defaults to "message".
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
		switch($this->getParameter('mode', 'to_string')) {
			case 'full':
				return $message;
			case 'message':
				return $message->getMessage();
			case 'parameter':
				return $message->getParameter($this->getParameter('parameter', 'message'));
			default:
				return $message->__toString();
		}
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviException is the base class for all Agavi related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviException extends Exception
{
	/**
	 * Print the stack trace for this exception.
	 *
	 * @param      string The format you wish to use for printing. Options
	 *                    include:
	 *                    - html
	 *                    - plain
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.9.0
	 */
	public static function printStackTrace(Exception $e, $context = null)
	{
		// throw away any response data that might be there
		if($context !== null && ($r = $context->getResponse()) !== null) {
			if($r->isLocked()) {
				// reponse is locked, so grab the output and discard it
				ob_start();
				$r->send();
				ob_end_clean();
			} else {
				// not locked, we can clear the response
				$r->clear();
			}
		}
		
		if($context !== null && ($r = $context->getResponse()) !== null && ($oti = $r->getOutputTypeInfo()) !== null && isset($oti['exception'])) {
			include($oti['exception']);
		} else {
			// include proper exception template
			include(AgaviConfig::get('exception.default_template'));
		}
		
		// bail out
		exit;
	}
}

?>
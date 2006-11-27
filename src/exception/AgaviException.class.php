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
	 * @param      Exception     The original exception.
	 * @param      AgaviContext  The context instance.
	 * @param      AgaviResponse The response instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.9.0
	 */
	public static function printStackTrace(Exception $e, AgaviContext $context = null, AgaviResponse $response = null)
	{
		// throw away any response data that might be there
		if($context !== null && ($c = $context->getController()) !== null && $response !== null) {
			if($response->isLocked()) {
				// reponse is locked, so grab the output and discard it
				ob_start();
				$response->send();
				ob_end_clean();
			} else {
				// not locked, we can clear the response
				$response->clear();
			}
		}
		
		if($context !== null && ($c = $context->getController()) !== null && ($oti = $c->getOutputTypeInfo()) !== null && isset($oti['exception_template'])) { 
			// an exception template was defined for this output type
			include($oti['exception_template']); 
		} elseif($context !== null && $tpl = AgaviConfig::get('exception.templates.' . $context->getName())) {
			// a template was set for this context
			include($tpl);
		} else {
			// include default exception template
			include(AgaviConfig::get('exception.default_template'));
		}
		
		// bail out
		exit;
	}
}

?>
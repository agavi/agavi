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
	public static function printStackTrace(Exception $e, AgaviContext $context = null, AgaviExecutionContainer $container = null)
	{
		// discard any previous output waiting in the buffer
		while(@ob_end_clean());
		
		if($context !== null && $container !== null && $container->getExceptionTemplate() !== null) { 
			// an exception template was defined for the container's output type
			include($container->getExceptionTemplate() !== null); 
		} elseif($container === null && $context->getController() !== null && $context->getController()->getOutputType() !== null && $context->getController()->getOutputType()->getExceptionTemplate() !== null) {
			// an exception template was defined for the default output type and no container was given
			include($context->getController()->getOutputType()->getExceptionTemplate());
		} elseif($context !== null && AgaviConfig::get('exception.templates.' . $context->getName()) !== null) {
			// a template was set for this context
			include(AgaviConfig::get('exception.templates.' . $context->getName()));
		} else {
			// include default exception template
			include(AgaviConfig::get('exception.default_template'));
		}
		
		// bail out
		exit;
	}
}

?>
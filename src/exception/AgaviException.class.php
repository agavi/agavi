<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated Superseded by AgaviException::render()
	 */
	public static function printStackTrace(Exception $e, AgaviContext $context = null, AgaviExecutionContainer $container = null)
	{
		return self::render($e, $context, $container);
	}
	
	/**
	 * Pretty-print this exception using a template.
	 *
	 * @param      Exception     The original exception.
	 * @param      AgaviContext  The context instance.
	 * @param      AgaviResponse The response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function render(Exception $e, AgaviContext $context = null, AgaviExecutionContainer $container = null)
	{
		// discard any previous output waiting in the buffer
		while(@ob_end_clean());
		
		if($container !== null && $container->getOutputType() !== null && $container->getOutputType()->getExceptionTemplate() !== null) { 
			// an exception template was defined for the container's output type
			include($container->getOutputType()->getExceptionTemplate()); 
			exit;
		}
		
		if($context !== null && $context->getController() !== null) {
			try {
				// check if an exception template was defined for the default output type
				if($context->getController()->getOutputType()->getExceptionTemplate() !== null) {
					include($context->getController()->getOutputType()->getExceptionTemplate());
					exit;
				}
			} catch(Exception $e2) {
				unset($e2);
			}
		}
		
		if($context !== null && AgaviConfig::get('exception.templates.' . $context->getName()) !== null) {
			// a template was set for this context
			include(AgaviConfig::get('exception.templates.' . $context->getName()));
			exit;
		}
		
		// include default exception template
		include(AgaviConfig::get('exception.default_template'));
		
		// bail out
		exit;
	}
}

?>
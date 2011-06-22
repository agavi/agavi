<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviDispatchFilter is the last in the chain of global filters and executes
 * the execution container, also re-setting the container's response to the
 * return value of the execution, so responses from forwards are passed along
 * properly.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDispatchFilter extends AgaviFilter implements AgaviIGlobalFilter
{
	/**
	 * Execute this filter.
	 *
	 * The DispatchFilter executes the execution container.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$container->setResponse($container->execute());
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * AgaviFilter provides a way for you to intercept incoming requests or outgoing
 * responses.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviIFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain A FilterChain instance.
	 * @param      AgaviResponse A Response instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response);

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function getContext();

	/**
	 * Initialize this Filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Filter.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array());
}

?>
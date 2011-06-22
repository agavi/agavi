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
 * AgaviFilter provides a way for you to intercept incoming requests or outgoing
 * responses.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviFilter extends AgaviParameterHolder implements AgaviIFilter
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

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
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		$this->setParameters($parameters);
	}
	
	/**
	 * The default "execute once" method, which just calls the regular one.
	 *
	 * @param      AgaviFilterChain        A FilterChain instance.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$this->execute($filterChain, $container);
	}
	
	/**
	 * The default "execute" method, which just calls continues in the chain.
	 *
	 * @param      AgaviFilterChain        A FilterChain instance.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$filterChain->execute($container);
	}
}

?>
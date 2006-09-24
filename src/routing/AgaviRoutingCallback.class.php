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
 * AgaviRoutingCallback allows you to provide callbacks into the routing
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviRoutingCallback
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        array An array with information about the route.
	 */
	protected $route = null;

	/**
	 * Initialize the callback instance.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array with information about the route.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array &$route)
	{
		$this->context = $context;
		$this->route =& $route;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @since      0.10.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	public function onMatched(array &$parameters)
	{
		return true;
	}

	public function onNotMatched()
	{
		return;
	}

	public function onGenerate(array $defaultParameters, array &$userParameters)
	{
		return $defaultParameters;
	}
}

?>
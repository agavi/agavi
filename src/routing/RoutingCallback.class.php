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
	protected $context = null,
						$route = null;

	public function initialize(AgaviContext $context, &$route)
	{
		$this->context = $context;
		$this->route =& $route;
	}

	public final function getContext()
	{
		return $this->context;
	}

	public function onMatched(&$params)
	{
		return true;
	}

	public function onNotMatched()
	{
		return;
	}

	public function onGenerate($params)
	{
		return $params;
	}
}

?>
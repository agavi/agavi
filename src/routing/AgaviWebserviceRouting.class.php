<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviWebserviceRouting handles the routing for Web Service requests.
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.11.0
 *
 * @version    $Id: AgaviWebRouting.class.php 1095 2006-10-07 15:53:10Z david $
 */
class AgaviWebserviceRouting extends AgaviRouting
{
	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviResponse $response, array $parameters = array())
	{
		parent::initialize($response, $parameters);
		
		if(!AgaviConfig::get("core.use_routing", false)) {
			return;
		}
		
		$rq = $this->context->getRequest();
		
		$this->input = $rq->getCalledMethod();
	}
}

?>

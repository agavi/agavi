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
 * AgaviRoutingUserSource allows you to provide an user source for the routing
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id: AgaviRoutingCallback.class.php 795 2006-07-22 09:18:07Z v-dogg $
 */
class AgaviRoutingUserSource implements AgaviIRoutingSource
{
	/**
	 * @var        AgaviISecurityUser An user instance.
	 */
	protected $user = null;

	/**
	 * Constructor.
	 *
	 * @param      AgaviISecurityUser An user instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(AgaviISecurityUser $user)
	{
		$this->user = $user;
	}

	/**
	 * Retrieves the source for a given entry.
	 *
	 * @param      array        An array with the parts of the entry.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSource($parts)
	{
		if($parts[0] == 'authenticated') {
			return (int) $this->user->isAuthenticated();
		} elseif($parts[0] == 'credentials' && count($parts) > 1) {
			// throw the 'credentials' entry away and check with the parameters left
			array_shift($parts);
			return (int) $this->user->hasCredential($parts);
		}

		return null;
	}
}

?>
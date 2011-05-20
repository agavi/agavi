<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
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
	 * Retrieves the value for a given entry from the source.
	 *
	 * @param      array An array with the name parts for the entry.
	 * 
	 * @return     mixed The value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSource(array $parts)
	{
		if($parts[0] == 'authenticated') {
			return (int) $this->user->isAuthenticated();
		} elseif($parts[0] == 'credentials' && count($parts) > 1) {
			// throw the 'credentials' entry away and check with the parameters left
			array_shift($parts);
			return (int) $this->user->hasCredentials($parts);
		}

		return null;
	}
}

?>
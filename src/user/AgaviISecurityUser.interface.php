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
 * AgaviSecurityUser provides advanced security manipulation methods.
 *
 * @package    agavi
 * @subpackage user
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviISecurityUser
{
	/**
	 * Add a credential to this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function addCredential($credential);

	/**
	 * Clear all credentials associated with this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function clearCredentials();

	/**
	 * Indicates whether or not this user has a credential.
	 *
	 * @param      mixed Credential data.
	 *
	 * @return     bool true, if this user has the credential, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function hasCredentials($credential);

	/**
	 * Indicates whether or not this user is authenticated.
	 *
	 * @return     bool true, if this user is authenticated, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function isAuthenticated();

	/**
	 * Remove a credential from this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function removeCredential($credential);

	/**
	 * Set the authenticated status of this user.
	 *
	 * @param      bool A flag indicating the authenticated status of this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function setAuthenticated($authenticated);

}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * SecurityUser provides advanced security manipulation methods.
 *
 * @package    agavi
 * @subpackage user
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     3.0.0
 * @version   $Id$
 */
abstract class SecurityUser extends User
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Add a credential to this user.
	 *
	 * @param mixed Credential data.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function addCredential ($credential);

	// -------------------------------------------------------------------------

	/**
	 * Clear all credentials associated with this user.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function clearCredentials ();

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not this user has a credential.
	 *
	 * @param mixed Credential data.
	 *
	 * @return bool true, if this user has the credential, otherwise false.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function hasCredential ($credential);

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not this user is authenticated.
	 *
	 * @return bool true, if this user is authenticated, otherwise false.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function isAuthenticated ();

	// -------------------------------------------------------------------------

	/**
	 * Remove a credential from this user.
	 *
	 * @param mixed Credential data.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function removeCredential ($credential);

	// -------------------------------------------------------------------------

	/**
	 * Set the authenticated status of this user.
	 *
	 * @param bool A flag indicating the authenticated status of this user.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function setAuthenticated ($authenticated);

}

?>

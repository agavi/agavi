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
 * AgaviBasicSecurityUser will handle any type of data as a credential.
 *
 * @package    agavi
 * @subpackage user
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviBasicSecurityUser extends AgaviSecurityUser
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	/**
	 * The namespace under which authenticated status will be stored.
	 */
	const AUTH_NAMESPACE = 'org.agavi.user.BasicSecurityUser.authenticated';

	/**
	 * The namespace under which credentials will be stored.
	 */
	const CREDENTIAL_NAMESPACE = 'org.agavi.user.BasicSecurityUser.credentials';

	private
		$authenticated = null,
		$credentials   = null;

	/**
	 * Add a credential to this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function addCredential ($credential)
	{

		if (!in_array($credential, $this->credentials))
		{

			$this->credentials[] = $credential;

		}

	}

	/**
	 * Clear all credentials associated with this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function clearCredentials ()
	{

		$this->credentials = null;
		$this->credentials = array();

	}

	/**
	 * Indicates whether or not this user has a credential.
	 *
	 * @param      mixed Credential data. Either a string or an array of
	 *                   credentials which are all required. If these individual
	 *                   credentials are again an array of credentials, one or
	 *                   more of these sub-credentials will be required.
	 *
	 * @return     bool true, if this user has the credential, otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function hasCredentials ($credential)
	{
		if(is_array($credential))
		{
			$credentials = (array)$credential;
			foreach($credentials as $credential)
			{
				if(is_array($credential))
				{
					foreach($credential as $subcred)
					{
						if(in_array($subcred, $this->credentials, true))
						{
							continue 2;
						}
					}
					return false;
				}
				else
				{
					if(!in_array($credential, $this->credentials, true))
					{
						return false;
					}
				}
			}
			return true;
		}
		else
		{
			return (in_array($credential, $this->credentials, true));
		}
	}

	/**
	 * Initialize this User.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this User.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		// initialize parent
		parent::initialize($context, $parameters);

		// read data from storage
		$storage = $this->getContext()->getStorage();

		$this->authenticated = $storage->read(self::AUTH_NAMESPACE);
		$this->credentials   = $storage->read(self::CREDENTIAL_NAMESPACE);

		if($this->authenticated == null) {
			// initialize our data
			$this->authenticated = false;
			$this->credentials   = array();
		}
	}

	/**
	 * Indicates whether or not this user is authenticated.
	 *
	 * @return     bool true, if this user is authenticated, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function isAuthenticated ()
	{

		return $this->authenticated;

	}

	/**
	 * Remove a credential from this user.
	 *
	 * @param      mixed Credential data.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function removeCredential ($credential)
	{

		if ($this->hasCredentials($credential))
		{

			// we have the credential, now we have to find it
			// let's not foreach here and do exact instance checks
			// for future safety
			for ($i = 0, $z = count($this->credentials); $i < $z; $i++)
			{

				if ($credential == $this->credentials[$i])
				{

				    // found it, let's nuke it
				    unset($this->credentials[$i]);
				    return;

				}

			}

		}

	}

	/**
	 * Set the authenticated status of this user.
	 *
	 * @param      bool A flag indicating the authenticated status of this user.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAuthenticated ($authenticated)
	{

		if ($authenticated === true)
		{

			$this->authenticated = true;

			return;

		}

		$this->authenticated = false;

	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{

		$storage = $this->getContext()
						->getStorage();

		// write credentials to the storage
		$storage->write(self::AUTH_NAMESPACE,       $this->authenticated);
		$storage->write(self::CREDENTIAL_NAMESPACE, $this->credentials);

		// call the parent shutdown method
		parent::shutdown();

	}

}

?>
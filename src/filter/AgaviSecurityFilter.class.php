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
 * AgaviBasicSecurityFilter checks security by calling the getCredentials() 
 * method of the action. Once the credential has been acquired, 
 * AgaviBasicSecurityFilter verifies the user has the same credential 
 * by calling the hasCredentials() method of SecurityUser.
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
class AgaviSecurityFilter extends AgaviFilter implements AgaviIActionFilter, AgaviISecurityFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        A FilterChain instance.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		// get the cool stuff
		$context    = $this->getContext();
		$user       = $context->getUser();

		// get the current action instance
		$actionInstance = $container->getActionInstance();

		// get the credential required for this action
		$credential = $actionInstance->getCredentials();

		if(!$actionInstance->isSecure()) {
			// the action instance does not require authentication, so we can continue in the chain and then bail out early
			try {
				return $filterChain->execute($container);
			} catch(AgaviSecurityException $e) {
				// authorization filter threw an exception, handle it
				$container->setNext($container->createSystemActionForwardContainer('secure'));
				return;
			}
		}

		// credentials can be anything you wish; a string, array, object, etc.
		// as long as you add the same exact data to the user as a credential,
		// it will use it and authorize the user as having the credential
		//
		// NOTE: the nice thing about the Action class is that getCredential()
		//       is vague enough to describe any level of security and can be
		//       used to retrieve such data and should never have to be altered
		$notAuthorized = false;
		if($user->isAuthenticated() && ($credential === null || $user->hasCredentials($credential))) {
			// the user has access, continue
			try {
				$filterChain->execute($container);
			} catch(AgaviSecurityException $e) {
				$notAuthorized = true;
			}
		} else {
			$notAuthorized = true;
		}
		if($notAuthorized) {
			if($user->isAuthenticated()) {
				// the user doesn't have access
				$container->setNext($container->createSystemActionForwardContainer('secure'));
			} else {
				// the user is not authenticated
				$container->setNext($container->createSystemActionForwardContainer('login'));
			}
		}
	}
}

?>
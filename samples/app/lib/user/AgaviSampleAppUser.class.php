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

class AgaviSampleAppUser extends AgaviRbacSecurityUser
{
	/**
	 * Let's pretend this is our database. For the sake of example ;)
	 */
	static $users = array(
		'Chuck Norris' => array(
			'salt' => 'bb6cb0a1ea7b94d9a1ffdfe74a3e141a',
			'password' => 'd436130cf2f5024cfdb3aa7325322d530336b95f', // that's "kick" plus the salt
			'roles' => array(
				'photographer',
			)
		),
	);
	
	public function startup()
	{
		parent::startup();
		
		$reqData = $this->getContext()->getRequest()->getRequestData();
		
		if(!$this->isAuthenticated() && $reqData->hasCookie('autologon')) {
			$login = $reqData->getCookie('autologon');
			try {
				$this->login($login['username'], $login['password'], true);
			} catch(AgaviSecurityException $e) {
				$response = $this->getContext()->getController()->getGlobalResponse();
				// login didn't work. that cookie sucks, delete it.
				$response->setCookie('autologon[username]', false);
				$response->setCookie('autologon[password]', false);
			}
		}
	}
	
	public function login($username, $password, $isPasswordHashed = false)
	{
		if(!isset(self::$users[$username])) {
			throw new AgaviSecurityException('username');
		}
		
		if(!$isPasswordHashed) {
			$password = self::computeSaltedHash($password, self::$users[$username]['salt']);
		}
		
		if($password != self::$users[$username]['password']) {
			throw new AgaviSecurityException('password');
		}
		
		$this->setAuthenticated(true);
		$this->clearCredentials();
		$this->grantRoles(self::$users[$username]['roles']);
	}
	
	public static function computeSaltedHash($secret, $salt)
	{
		// sha1 is flawed. you know the drill. this is just an example.
		return sha1($secret . $salt);
	}
	
	public static function getPassword($username)
	{
		if(self::$users[$username]) {
			return self::$users[$username]['password'];
		}
	}
	
	public function logout()
	{
		$this->clearCredentials();
		$this->setAuthenticated(false);
	}
}

?>
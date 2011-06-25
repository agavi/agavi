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

class AgaviSampleAppUser extends AgaviRbacSecurityUser implements Zend_Acl_Role_Interface
{
	/**
	 * Let's pretend this is our database. For the sake of example ;)
	 */
	static $users = array(
		'chuck.norris' => array(
			'password' => '$2a$10$2/Gmc4XpwAytFgy3wfrW9OUnkzd6ahgcMqrm4cEc4zD3IFD1GB6IG', // bcrypt, 10 rounds, "kick"
			'role' => 'hero',
		),
		'jack.bauer' => array(
			'password' => '$2a$10$3OGZhOGU2MTNlOTI4YjQz.AM5Ej6gG1VX9KDCJ52PmBnAiQYSe18S', // bcrypt, 10 rounds, "ctu"
			'role' => 'agent',
		),
		'joe.cool' => array(
			'password' => '$2a$10$8ZWRjYTcwMWY5YTEzMGE5OIxOCBdJ0l2S7VWCMlzA31yPueKDBZli', // bcrypt, 10 rounds, "redbaron"
			'role' => 'user',
		),
	);
	
	protected $zendAcl;
	
	public function isAllowed($resource, $operation)
	{
		return $this->getZendAcl()->isAllowed($this, $resource, $operation);
	}
	
	public function getZendAcl()
	{
		return $this->zendAcl;
	}
	
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$this->zendAcl = new Zend_Acl();
		
		$this->zendAcl->addRole('user');
		$this->zendAcl->addRole('agent', 'user');
		$this->zendAcl->addRole('hero', 'agent');
		
		$this->zendAcl->addResource('product');
		$this->zendAcl->addResource('secretproduct', 'product');
		
		$this->zendAcl->allow(null, 'product', 'read');
		$this->zendAcl->deny(null, 'secretproduct', 'read');
		$this->zendAcl->deny(null, 'product', 'write');
		$this->zendAcl->allow('user', 'secretproduct', 'read', new AgaviSampleAppIsProductOwnerAssertion());
		$this->zendAcl->allow('user', array('product', 'secretproduct'), 'write', new AgaviSampleAppIsProductOwnerAssertion());
		$this->zendAcl->allow('agent', 'product', 'write');
		$this->zendAcl->allow('hero', 'secretproduct', array('read', 'write'));
	}
	
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
			$password = self::computeSaltedHash($password, self::$users[$username]['password']);
		}
		
		if($password != self::$users[$username]['password']) {
			throw new AgaviSecurityException('password');
		}
		
		$this->setAuthenticated(true);
		$this->clearCredentials();
		
		$this->setAttribute('role', self::$users[$username]['role']);
		$this->setAttribute('username', $username);
	}
	
	public static function computeSaltedHash($secret, $salt)
	{
		return crypt($secret, $salt);
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
		$this->removeAttribute('role');
		$this->removeAttribute('username');
	}
	
	public function getRoleId()
	{
		if($this->isAuthenticated()) {
			return $this->getAttribute('role');
		}
		return 'user';
	}
}

?>
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

class AgaviSampleAppUser extends AgaviSecurityUser
{
	public function login($username, $password)
	{
		if($username != 'Chuck Norris') {
			throw new AgaviSecurityException('username');
		}
		
		if($password != 'kick') {
			throw new AgaviSecurityException('password');
		}
		
		$this->setAuthenticated(true);
		$this->clearCredentials();
		$this->addCredential('admin');
	}
	
	public function logout()
	{
		$this->clearCredentials();
		$this->setAuthenticated(false);
	}
}

?>
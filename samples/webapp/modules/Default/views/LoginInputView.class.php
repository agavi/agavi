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

class Default_LoginInputView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute()
	{
		// set our template
		$this->setTemplate('LoginInput');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', 'Login');
		
		// our login form is displayed. so let's remove that cookie thing there
		$this->getResponse()->setCookie('autologon[username]', false);
		$this->getResponse()->setCookie('autologon[password]', false);
		
		if($this->getContext()->getRequest()->hasAttributeNamespace('org.agavi.controller.forwards.login')) {
			// we were redirected to the login form by the controller because the requested action required security
			// so store the input URL in the session for a redirect after login
			$url = 
				'http' . (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 's' : '')  . '://' . 
				$_SERVER['SERVER_NAME'] . 
				(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? ($_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '') : ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '')) . 
				$_SERVER['REQUEST_URI']
			;
			$this->getContext()->getUser()->setAttribute('redirect', $url, 'org.agavi.SampleApp.login');
		} elseif($this->getContext()->getRequest()->getMethod() == 'read') {
			// clear the redirect URL just to be sure
			// but only if request method is "read", i.e. if the login form is served via GET!
			$this->getContext()->getUser()->removeAttribute('redirect', 'org.agavi.SampleApp.login');
		}
	}

}

?>
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

class Default_LoginSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		$usr = $this->getContext()->getUser();
		$url = false;
		if($usr->hasAttribute('redirect', 'org.agavi.SampleApp.login')) {
			/*
				we need to redirect back to the action that caused the login form to
				pop up. setting no template will mean no rendering is performed, which
				is a good thing since we'll redirect anyway.
				response will be locked, so no output will be added. the redirect does,
				however, return a Response instance that can be used to set cookies etc.
				action and global filters will still run back to Controller::dispatch(),
				a redirect in 0.11 does NOT bail out immediately anymore!
			*/
			$url = $usr->getAttribute('redirect', 'org.agavi.SampleApp.login');
			$usr->removeAttribute('redirect', 'org.agavi.SampleApp.login');
			$res = $this->getContext()->getController()->redirect($url);
		} else {
			$res = $this->getResponse();
		}
		
		// set the autologon cookie if requested
		if($parameters->hasParameter('remember')) {
			$res->setCookie('autologon[username]', $parameters->getParameter('username'), 60*60*24*14);
			$res->setCookie('autologon[password]', $parameters->getParameter('password'), 60*60*24*14);
		}
		
		// we redirected, let's bail out
		if($url !== false) {
			return;
		}
		
		// set our template
		$this->setTemplate('LoginSuccess');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Login Successful', 'default.Login'));
	}

}

?>
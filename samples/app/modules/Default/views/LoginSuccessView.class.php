<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class Default_LoginSuccessView extends AgaviSampleAppDefaultBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$usr = $this->getContext()->getUser();
		$res = $this->getResponse();

		// set the autologon cookie if requested
		if($rd->hasParameter('remember')) {
			$res->setCookie('autologon[username]', $rd->getParameter('username'), 60*60*24*14);
			$res->setCookie('autologon[password]', $usr->getPassword($rd->getParameter('username')), 60*60*24*14);
		}

		if($usr->hasAttribute('redirect', 'org.agavi.SampleApp.login')) {
			$this->getResponse()->setRedirect($usr->removeAttribute('redirect', 'org.agavi.SampleApp.login'));
			return;
		}

		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Login Successful', 'default.Login'));
	}

}

?>
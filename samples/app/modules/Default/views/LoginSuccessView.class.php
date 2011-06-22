<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
		$res = $this->getResponse();

		// set the autologon cookie if requested
		if($rd->hasParameter('remember')) {
			$res->setCookie('autologon[username]', $rd->getParameter('username'), '+14 days');
			$res->setCookie('autologon[password]', $this->us->getPassword($rd->getParameter('username')), '+14 days');
		}

		if($this->us->hasAttribute('redirect', 'org.agavi.SampleApp.login')) {
			$this->getResponse()->setRedirect($this->us->removeAttribute('redirect', 'org.agavi.SampleApp.login'));
			return;
		}

		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->tm->_('Login Successful', 'default.Login'));
	}

}

?>
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

class Default_LoginErrorView extends AgaviSampleAppDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		
		// set the title
		$this->setAttribute('_title', $this->tm->_('Login', 'default.Login'));
		
		// set error messages from the user login method
		if(($error = $this->getAttribute('error')) !== null) {
			$this->container->getValidationManager()->setError($error, $this->context->getTranslationManager()->_('Wrong ' . ucfirst($error), 'default.errors.Login'));
		}
		
		// use the input template, default would be LoginError, but that doesn't exist
		$this->getLayer('content')->setTemplate('LoginInput');
	}
}

?>
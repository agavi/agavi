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

class Default_SecureSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// set our template
		$this->setTemplate('SecureSuccess');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Permission Denied', 'default.ErrorActions'));
	}

}

?>
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

class Default_Error404SuccessView extends AgaviView
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
		$this->addLayer('content', 'Error404Success');
		$this->addLayer('decorator', 'Master');

		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));
		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('404 Not Found', 'default.ErrorActions'));

		$this->getResponse()->setHttpStatusCode('404');
	}

}

?>
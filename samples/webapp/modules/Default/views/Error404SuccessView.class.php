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
		// get the request
		$request = $this->getContext()->getRequest();

		// set our template
		$this->setTemplate('Error404Success');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', '404 Not Found');

		// set originally requested module/action attributes
		// these attributes are provided by the controller in the event
		// of a 404 error
		$this->setAttribute('requested_module', $request->getAttribute('requested_module'));
		$this->setAttribute('requested_action', $request->getAttribute('requested_action'));
		
		$this->getResponse()->setHttpStatusCode('404');
	}

}

?>
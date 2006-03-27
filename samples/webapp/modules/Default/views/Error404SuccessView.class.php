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

class Default_Error404SuccessView extends AgaviPHPView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute ()
	{

		// get the request
		$request = $this->getContext()->getRequest();

		// set our template
		$this->setTemplate('Error404Success.php');

		// set the title
		$this->setAttribute('title', 'Error 404 Action');

		// set originally requested module/action attributes
		// these attributes are provided by the controller in the event
		// of a 404 error
		$this->setAttribute('requested_module', $request->getAttribute('requested_module'));
		$this->setAttribute('requested_action', $request->getAttribute('requested_action'));

		// build our menu
		require_once(AgaviConfig::get('core.module_dir') . '/Default/lib/build_menu.php');

	}

}

?>
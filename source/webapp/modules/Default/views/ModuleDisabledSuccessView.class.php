<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

class ModuleDisabledSuccessView extends PHPView
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  1.0.0
	 */
	public function execute ()
	{

		// set our template
		$this->setTemplate('ModuleDisabledSuccess.php');

		// set the title
		$this->setAttribute('title', 'Module Disabled');

		// build our menu
		require_once(MO_MODULE_DIR . '/Default/lib/build_menu.php');

	}

}

?>

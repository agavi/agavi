<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

class Default_LoginAction extends Action
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any application/business logic for this action.
	 *
	 * In a typical database-driven application, execute() handles application
	 * logic itself and then proceeds to create a model instance. Once the model
	 * instance is initialized it handles all business logic for the action.
	 *
	 * A model should represent an entity in your application. This could be a
	 * user account, a shopping cart, or even a something as simple as a
	 * single product.
	 *
	 * @return mixed - A string containing the view name associated with this
	 *                 action, or...
	 *               - An array with three indices:
	 *                 0. The parent module of the view that will be executed.
	 *                 1. The parent action of the view that will be executed.
	 *                 2. The view that will be executed.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function execute ()
	{

		return View::INPUT;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the default view to be executed when a given request is not
	 * served by this action.
	 *
	 * @return mixed - A string containing the view name associated with this
	 *                 action, or...
	 *               - An array with three indices:
	 *                 0. The parent module of the view that will be executed.
	 *                 1. The parent action of the view that will be executed.
	 *                 2. The view that will be executed.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function getDefaultView ()
	{

		return View::INPUT;

	}

}

?>

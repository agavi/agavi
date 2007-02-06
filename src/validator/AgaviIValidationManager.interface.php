<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * abstract superclass for ValidationManagers
 * 
 * AgaviIValidationManager is the interface for all ValidationManagers
 * which control validation of request parameters, provide error messages and
 * handle the creation and management of the validators.
 * 
 * @package    agavi
 * @subpackage validator
 * 
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @since      0.11
 * 
 * @version:   $Id$
 */
interface AgaviIValidationManager
{
	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getContext();

	/**
	 * Initializes the validator manager
	 * 
	 * @param      AgaviContext The context.
	 * @param      array        The parameters for this validator manager.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array());

	/**
	 * Clears the validator manager for reuse
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear();

	/**
	 * Starts the validation process and returns the result
	 * 
	 * @param      AgaviRequestDataHolder The data which should be validated.
	 * 
	 * @return     bool The result of validation process.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviRequestDataHolder $parameters);
}

?>
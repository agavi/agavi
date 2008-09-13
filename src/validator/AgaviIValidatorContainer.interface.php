<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviIValidatorContainer is an interface for classes which contains several
 * child validators
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviIValidatorContainer
{
	/**
	 * Adds a new validator to the list of children.
	 * 
	 * @param      AgaviValidator new child
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addChild(AgaviValidator $validator);

	/**
	 * Adds a validation result for a given field.
	 *
	 * @param      AgaviValidator The validator.
	 * @param      string The name of the field which has been validated.
	 * @param      int    The result of the validation.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 * @deprecated 1.0.0
	 */
	public function addFieldResult($validator, $fieldname, $result);

	/**
	 * Adds a intermediate result of an validator for the given argument
	 *
	 * @param      AgaviValidationArgument The argument
	 * @param      int    The arguments result.
	 * @param      AgaviValidator The validator (if the error was cause inside 
	 *                            a validator).
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	//public function addArgumentResult(AgaviValidationArgument $argument, $result, $validator = null);

	/**
	 * Adds an incident to the validation result. 
	 *
	 * @param      AgaviValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function addIncident(AgaviValidationIncident $incident);

	/**
	 * Returns a named child validator.
	 *
	 * @param      AgaviValidator The child validator.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getChild($name);

	/**
	 * Returns all child validators.
	 *
	 * @return     array An array of AgaviValidator instances.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getChilds();

	/**
	 * Fetches the dependency manager
	 * 
	 * @return     AgaviDependencyManager The dependency manager to be used
	 *                                    by child validators.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getDependencyManager();

}
?>
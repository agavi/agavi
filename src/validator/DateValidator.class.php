<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Authors                                                                   |
// |  Sean Kerr (skerr@mojavi.org)                                             |
// |  Agavi Foundation (info@agavi.org)                                        |
// | Copyright (c) 2003-2005  Authors                                          |
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
 * DateValidator verifies a parameter is of a date format.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.0
 * @version   $Id$
 */
class DateValidator extends Validator
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute this validator.
	 *
	 * @param mixed A file or parameter value/array.
	 * @param error An error message reference.
	 *
	 * @return bool true, if this validator executes successfully, otherwise
	 *              false.
	 *
	 * @author Bob Zoller (bzoller@agavi.org)
	 * @since  1.0
	 */
	public function execute (&$value, &$error)
	{
		if (empty($value) || (strtotime($value) == -1)) {
			$error = $this->getParameter('error');
			return false;
		}
		return true;
	}

	/**
	 * Initialize this validator.
	 *
	 * @param Context The current application context.
	 * @param array   An associative array of initialization parameters.
	 *
	 * @return bool true, if initialization completes successfully, otherwise
	 *              false.
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  1.0
	 */
	public function initialize ($context, $parameters = null)
	{
		$this->setParameter('error', 'Date is not valid.');
		parent::initialize($context, $parameters);
	}

}

?>

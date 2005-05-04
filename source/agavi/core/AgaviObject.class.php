<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * $Id$
 *
 * AgaviObject provides useful methods that all Agavi classes inherit.
 *
 * @package    agavi
 * @subpackage core
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     3.0.0
 * @version   $Rev$
 */
abstract class AgaviObject
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Retrieve a string representation of this object.
	 *
	 * @return string A string containing all public variables available in
	 *                this object.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function toString ()
	{

		$output = '';
		$vars   = get_object_vars($this);

		foreach ($vars as $key => &$value)
		{

			if (strlen($output) > 0)
			{

				$output .= ', ';

			}

			$output .= $key . ': ' . $value;

		}

		return $output;

	}

}

?>

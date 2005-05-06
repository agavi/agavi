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
 * ForwardException is thrown when an error occurs while attempting to forward
 * the request.
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     3.0.0
 * @version   $Id$
 */
class ForwardException extends AgaviException
{

	// +-----------------------------------------------------------------------+
	// | CONSTRUCTOR                                                           |
	// +-----------------------------------------------------------------------+

	/**
	 * Class constructor.
	 *
	 * @param string The error message.
	 * @param int    The error code.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function __construct ($message = null, $code = 0)
	{

		parent::__construct($message, $code);

		$this->setName('ForwardException');

	}

}

?>

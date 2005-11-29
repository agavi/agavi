<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 Agavi Foundation                                  |
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
 * UnreadableException is thrown when a configuration file could not be found or is unreadable.
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author    David Zuelke (dz@bitxtender.com)
 * @author    Agavi Foundation (info@agavi.org)
 * @since     0.10.0
 * @version   $Id$
 */
class UnreadableException extends ConfigurationException
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
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function __construct ($message = null, $code = 0)
	{

		parent::__construct($message, $code);

		$this->setName('UnreadableException');

	}

}

?>

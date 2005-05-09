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
 * Layout allows you to specify a message layout for log messages.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     1.0.0
 * @version   $Id$
 */
abstract class Layout extends AgaviObject
{

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+

	private
		$layout = null;

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Format a message.
	 *
	 * @param Message A Message instance.
	 *
	 * @return string A formatted message.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  1.0.0
	 */
	abstract function & format ($message);

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the message layout.
	 *
	 * @return string A message layout.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function getLayout ()
	{

		return $this->layout;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set the message layout.
	 *
	 * @param string A message layout.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function setLayout ($layout)
	{

		$this->layout = $layout;

	}

}

?>

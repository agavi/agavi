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
 * Appender allows you to specify a destination for log data and provide
 * a custom layout for it, through which all log messages will be formatted.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     1.0.0
 * @version   $Id$
 */
abstract class Appender extends AgaviObject
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
	 * Retrieve the layout.
	 *
	 * @return Layout A Layout instance, if one has been set, otherwise null.
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  1.0.0
	 */
	public function getLayout ()
	{

		return $this->layout;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set the layout.
	 *
	 * @param Layout A Layout instance.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  1.0.0
	 */
	public function setLayout ($layout)
	{

		$this->layout = $layout;

	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	abstract function shutdown ();

	// -------------------------------------------------------------------------

	/**
	 * Write log data to this appender.
	 *
	 * @param string Log data to be written.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  1.0.0
	 */
	abstract function write (&$data);

}

?>

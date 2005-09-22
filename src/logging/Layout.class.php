<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 Agavi Foundation.                                 |
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
 * Layout allows you to specify a message layout for log messages.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Authors
 * @since     0.9.0
 * @version   $Id$
 */
abstract class Layout extends AgaviObject
{

	private $layout = null;

	/**
	 * Initialize the Layout.
	 * 
	 * @access public
	 * @param array An array of parameters.
	 * @return void
	 * @author Veikko Mäkinen (mail@veikkomakinen.co)
	 */
	public function initialize($params)
	{
		/* empty so we're not required to override this */
	}

	/**
	 * Format a message.
	 *
	 * @param Message A Message instance.
	 *
	 * @return string A formatted message.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	abstract function & format ($message);

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the message layout.
	 *
	 * @return string A message layout.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
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
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function setLayout ($layout)
	{
		$this->layout = $layout;
	}

}

?>

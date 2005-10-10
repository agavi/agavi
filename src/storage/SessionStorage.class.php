<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
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
 * SessionStorage allows you to store persistent Agavi data in the user
 * session.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>auto_start</b>   - [Yes]    - Should session_start() automatically be
 *                                    called?
 * # <b>session_name</b> - [Agavi] - The name of the session.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
class SessionStorage extends Storage
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Initialize this Storage.
	 *
	 * @param Context A Context instance.
	 * @param array   An associative array of initialization parameters.
	 *
	 * @return bool true, if initialization completes successfully, otherwise
	 *              false.
	 *
	 * @throws <b>InitializationException</b> If an error occurs while
	 *                                        initializing this Storage.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function initialize ($context, $parameters = null)
	{

		parent::initialize($context, $parameters);

		$sessionName = $this->getParameter('session_name', 'Agavi');
		session_name($sessionName);
		
		if ($sessionId = $this->getParameter('session_id')) {
			session_id($sessionId);
		}
		
		if ($this->getParameter('auto_start', true)) {
			session_start();
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Read data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param string A unique key identifying your data.
	 *
	 * @return mixed Data associated with the key.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function & read ($key)
	{

		$retval = null;

		if (isset($_SESSION[$key]))	{
			$retval =& $_SESSION[$key];
		}
		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Remove data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param string A unique key identifying your data.
	 *
	 * @return mixed Data associated with the key.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function & remove ($key)
	{

		$retval = null;

		if (isset($_SESSION[$key]))
		{

			$retval =& $_SESSION[$key];

			unset($_SESSION[$key]);

		}

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function shutdown ()
	{

		// don't need a shutdown procedure because read/write do it in real-time

	}

	// -------------------------------------------------------------------------

	/**
	 * Write data to this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param string A unique key identifying your data.
	 * @param mixed  Data associated with your key.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function write ($key, &$data)
	{

		$_SESSION[$key] =& $data;

	}

}

?>

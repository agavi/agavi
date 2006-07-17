<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * ActionStack keeps a list of all requested actions and provides accessor
 * methods for retrieving individual entries.
 *
 * @package    agavi
 * @subpackage action
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviActionStack
{

	private
		$stack = array();

	/**
	 * Add an entry.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 * @param      AgaviAction An action implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function addEntry ($moduleName, $actionName, AgaviAction $actionInstance, AgaviParameterHolder $parameters)
	{

		// create our action stack entry and add it to our stack
		$actionEntry = new AgaviActionStackEntry($moduleName, $actionName,
						                    $actionInstance, $parameters);

		$this->stack[] = $actionEntry;

	}

	/**
	 * Retrieve the entry at a specific index.
	 *
	 * @param      int An entry index.
	 *
	 * @return     AgaviActionStackEntry An action stack entry implementation.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getEntry ($index)
	{

		$retval = null;

		if ($index > -1 && $index < count($this->stack))
		{

			$retval = $this->stack[$index];

		}

		return $retval;

	}

	/**
	 * Retrieve the first entry.
	 *
	 * @return     AgaviActionStackEntry An action stack entry implementation.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFirstEntry ()
	{

		$count  = count($this->stack);
		$retval = null;

		if ($count > 0)
		{

			$retval = $this->stack[0];

		}

		return $retval;

	}

	/**
	 * Retrieve the last entry.
	 *
	 * @return     AgaviActionStackEntry An action stack entry implementation.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLastEntry ()
	{

		$count  = count($this->stack);
		$retval = null;

		if ($count > 0)
		{

			$retval = $this->stack[$count - 1];

		}

		return $retval;

	}

	/**
	 * Retrieve the size of this stack.
	 *
	 * @return     int The size of this stack.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getSize ()
	{

		return count($this->stack);

	}
	
	/**
	 * Clear the stack.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->stack = array();
	}

}

?>
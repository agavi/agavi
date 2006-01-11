<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 the Agavi Project.                                |
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
 * ConsoleRequest provides support for console-only request information such as
 * command-line parameters.
 * 
 * @package    agavi
 * @subpackage request
 *
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class ConsoleRequest extends Request
{

	/**
	 * Initialize this Request.
	 *
	 * @param      Context A Context instance.
	 * @param      array   An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>InitializationException</b> If an error occurs while
	 *                                            initializing this Request.
	 *
	 * @author     Agavi Project <info@agavi.org>
	 * @since      0.9.0
	 */
	public function initialize ($context, $parameters = null)
	{

		// load parameters
		$this->loadParameters($context);

		// set the default method
		$this->setMethod(self::CONSOLE);

	}

	/**
	 * Loads command line parameters into the parameter list.
	 *
	 * @param      Context $context
	 * @return     void
	 *
	 * @author     Agavi Project <info@agavi.org>
	 * @since      0.9.0
	 */
	private function loadParameters ($context)
	{
		$shortopts = $context->getController()->getParameter('shortopts');
		if (!is_array($longopts = $context->getController()->getParameter('longopts'))) {
			$longopts = array();
		}

		if (($params = @getopt($shortopts, $longopts)) === false) {
			throw new AgaviException('Invalid getopt options');
		}

		$this->setParameters($params);

	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     Agavi Project <info@agavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{

		// nothing to do here

	}

}

?>
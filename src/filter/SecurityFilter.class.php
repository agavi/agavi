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
 * AgaviSecurityFilter provides a base class that classifies a filter as one that
 * handles security.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviSecurityFilter extends AgaviFilter
{

	/**
	 * Retrieve a new Controller implementation instance.
	 *
	 * @param      string A Controller implementation name.
	 *
	 * @return     AgaviController A Controller implementation instance.
	 *
	 * @throws     <b>AgaviFactoryException</b> If a security filter implementation
	 *                                          instance cannot be created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function newInstance ($class)
	{

		// the class exists
		$object = new $class();

		if (!($object instanceof AgaviSecurityFilter))
		{

			// the class name is of the wrong type
			$error = 'Class "%s" is not of the type SecurityFilter';
			$error = sprintf($error, $class);

			throw new AgaviFactoryException($error);

		}

		return $object;

	}

}

?>
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
 * An extension to AgaviModel, but for implementation as a Singleton
 * 
 * @package    agavi
 * @subpackage model
 * 
 * @since      0.10.0 
 * @author     Agavi Project <info@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 *
 * @version    $Id$
 */

abstract class AgaviSingletonModel extends AgaviModel
{
	protected static $instance = array();

	protected final function __construct() { }
	protected final function __clone() { }

	public static function getInstance($className)
	{
		$lowerClassName = strtolower($className);
		if (!isset(self::$instance[$lowerClassName]))
			self::$instance[$lowerClassName] = new $className();
		return self::$instance[$lowerClassName];
	}
}
?>
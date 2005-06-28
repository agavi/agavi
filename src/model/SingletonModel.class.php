<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Agavi Foundation                                 |
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
 * An extension to Model, but for implementation as a Singleton
 * 
 * @package agavi
 * @subpackage database
 * 
 * @since 1.0 
 * @author Agavi Foundation (info@agavi.org)
 * @author David Zuelke (dz@bitxtender.com)
 * @version $Id$
 */

	abstract class SingletonModel extends Model
	{
		protected static $instance = array();

		protected function __construct() { }

		public static function getInstance($className)
		{
			if(!isset(self::$instance[$className]))
				self::$instance[$className] = new $className();
			return self::$instance[$className];
		}
	}

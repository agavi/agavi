<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * A file that serves as the autoload file for class Propel
 * Use this line in autoload.ini to enable the magic:
 * <code>
 *   Propel      = "%core.agavi_dir%/database/PropelAutoload.php"
 * </code>
 * Whenever you want to use Propel now, it will automatically be setup for you
 * using the configuration file you specified in database.xml
 * 
 * @package    agavi
 * @subpackage database
 * 
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */

// we don't need a _once here
require('propel/Propel.php');

Propel::init(AgaviPropelDatabase::getDefaultConfigPath());

?>
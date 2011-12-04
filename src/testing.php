<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * bootstrap file for the AgaviTesting
 *
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */

$here = realpath(__DIR__);

// load Agavi basics
require_once($here . '/agavi.php');

// AgaviTesting class
require_once($here . '/testing/AgaviTesting.class.php');

// changing the init procedure in a minor release... good job, PHPUnit...
require_once('PHPUnit/Runner/Version.php'); 
if(version_compare(PHPUnit_Runner_Version::id(), '3.5.2', '<')) { 
	trigger_error('Agavi requires PHPUnit version 3.5.2 or higher', E_USER_ERROR);
}
// load PHPUnit basics
require_once('PHPUnit/Autoload.php');

?>
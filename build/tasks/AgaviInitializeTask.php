<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

require_once(dirname(__FILE__) . '/AgaviTask.php');

/**
 * Initializes the Agavi build environment.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <impl@cynigram.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id: AgaviInputTask.php 2319 2008-02-22 04:27:36Z impl $
 */
class AgaviInitializeTask extends AgaviTask
{
	/**
	 * Executes this task.
	 */
	public function main()
	{
		var_dump(get_class($this->project));
		$build = new PhingFile('agavi/build.php');
		require_once($build->getAbsolutePath());
		
		AgaviBuild::bootstrap();
	}
}

?>
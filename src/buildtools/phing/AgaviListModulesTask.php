<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * @package    agavi
 * @subpackage buildtools
 *
 * @author     Mike Vincent <mike@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviListModulesTask extends Task
{
	private
		$property,
		$defaultProperty,
		$app;

	public function setApp($dir)
	{
		$this->app = $dir;
	}

	public function setProperty($property)
	{
		$this->property = $property;
	}

	public function setDefaultproperty($property)
	{
		$this->defaultProperty = $property;
	}

	public function main()
	{
		if($this->app && $this->property) {
			$paths = glob($this->app.'/modules/*', GLOB_ONLYDIR);
			if($paths === false) {
				throw new BuildException('Could not glob() modules directory, please check access rights');
			}
			foreach($paths as $path) {
				$modules[] = basename($path);
			}
			if(isset($modules[0])) {
				$this->project->setProperty($this->defaultProperty, $modules[0]);
			}
			$this->project->setProperty($this->property, implode(',', $modules));
		} else {
			throw new BuildException('You must pass the path of the app directory and give a property name to hold the list.');
		}
	}
}

?>
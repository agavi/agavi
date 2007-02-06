<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviFixPathsTask extends Task {
	private $base,
					$depth=5,
					$newproject = false,
					$parseModulePath = false,
					$testing = false;

	private $appdir = 'app';

	public function setAppdir($appdir)
	{
		$this->appdir = $appdir;
	}
	public function setBase($base) {
		$this->base = $base;
	}
	
	public function setDepth($depth) {
		$this->depth = (int) $depth;
	}
	
	public function setDefaultmodule($boolean) {
		$this->parseModulePath = (boolean) $boolean;
	}
	
	public function setNew($boolean) {
		$this->newproject = (boolean) $boolean;
	}
	
	public function setTesting($boolean) {
		$this->testing = (boolean) $boolean;
	}
	
	private function getModule() {
		$module = '';
		if (preg_match('#/app/modules/(.*?)(/.*)?$#', str_replace('\\', '/', $this->base), $matches)) {
			$module = $matches[1];
		}
		return $module;
	}

	private function getDir($pattern = 'modules') {
		if($pattern == 'modules')
		{
			$pattern = '/'.$this->appdir.'/'.$pattern;
		}
		if ($this->newproject) { 
			return realpath($this->base);
		}

		$base = str_replace('\\', '/', $this->base);

		$needle = implode('/', array_diff(explode('/', $pattern), explode('/', ($base{0} != '/' ? '/' : '') . $base)));

		preg_match('#(.*?)' . $pattern . '#', $base . '/' . $needle, $matches);
		if (isset($matches[1]) && file_exists($matches[1] . $pattern)) {
			return realpath($matches[1]);
		} else {
			// above the project root folder
			if ($this->testing && $pattern != '/src/agavi.php') {
				return $this->getDir('/src/agavi.php');
			} else {
				return false;
			}
		}
	} 

	public function main() {
		$pdir = $this->getDir();
		if ($pdir) {
			echo "Project dir: $pdir\n";
			$this->project->setProperty('project.dir', $pdir);
			$this->project->setProperty('app.dir', realpath($pdir) . '/' . $this->appdir);
			$this->project->setProperty('tests.dir', realpath($pdir) . '/tests');
			if ($this->parseModulePath) {
				$this->project->setProperty('default.module', $this->getModule());
			}
		} else {
			throw new BuildException('Unable to determine the location of the project directory based on: '. $this->base);
		}
	}
}
?>
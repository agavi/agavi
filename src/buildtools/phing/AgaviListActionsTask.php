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
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviListActionsTask extends Task {
	private 	$property,
				$defaultProperty,
				$moduleDir;


	public function setModuleDir($dir) {
		$this->moduleDir = $dir;
	}

	public function setProperty($property) {
		$this->property = $property;
	}

	public function setDefaultproperty($property) {
		$this->defaultProperty = $property;
	}

	public function main() {
		if ($this->moduleDir && $this->property) {
			$actions = $this->getActions();
			$this->project->setProperty($this->defaultProperty, $actions[0]);
			$this->project->setProperty($this->property, implode(',', $actions));
		} else {
			throw new BuildException('You must pass the path of the modules/Module/actions directory and give a property name to hold the list.');
		}
	}

	protected function getActions() {

		/* code shamelessly copied from AgaviToolkit::clearCache */

		$result = array();

		$ignores = array('.', '..', '.svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr');

		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->moduleDir), RecursiveIteratorIterator::CHILD_FIRST) as $iterator) {
			$pathname = str_replace('\\', '/', $iterator->getPathname());

			$continue = false;
			if(in_array($iterator->getFilename(), $ignores)) {
				$continue = true;
			}
			else {
				foreach($ignores as $ignore) {
					if(strpos($pathname, '/' . $ignore . '/') !== false) {
						$continue = true;
						break;
					} elseif(strrpos($pathname, '/' . $ignore) == (strlen($pathname) - strlen('/' . $ignore))) {
						// if we hit the directory itself it wont include a trailing /
						$continue = true;
						break;
					}
				}
			}

			if($continue) {
				continue;
			}

			if($iterator->isFile()) {
				$matches = array();
				if (preg_match("/\/actions\/(.+)Action\.class\.php/", $pathname, $matches)) {
					$result[] = str_replace('/', '.', $matches[1]);
				}
			}
		}

		return $result;

	}

}
?>
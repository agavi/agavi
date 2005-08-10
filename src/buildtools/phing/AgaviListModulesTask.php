<?php

class AgaviListModulesTask extends Task {
	private $property,
					$webapp;

	public function setWebapp($dir) {
		$this->webapp = $dir;
	}

	public function setProperty($property) {
		$this->property = $property;
	}
	
	public function main() {
		if ($this->webapp && $this->property) {
			foreach (glob($this->webapp.'/modules/*', GLOB_ONLYDIR) as $path) {
				$modules[] = basename($path);
			}
			$this->project->setProperty($this->property, implode(',', $modules));
		} else {
			throw new BuildException('You must pass the path of the Webapp directory and give a property name to hold the list.');
		}
	}
}
?>

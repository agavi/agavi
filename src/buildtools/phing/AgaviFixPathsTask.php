<?php
class AgaviFixPathsTask extends Task {
	private $base,
					$depth=5,
					$newproject = false,
					$testing=false;

	public function setBase($base) {
		$this->base = $base;
	}
	
	public function setDepth($depth) {
		$this->depth = (int) $depth;
	}
	
	public function setNew($boolean) {
		$this->newproject = (boolean) $boolean;
	}
	
	public function setTesting($boolean) {
		$this->testing = (boolean) $boolean;
	}

	private function getDir($pattern = '/webapp') {
		if ($this->newproject || file_exists($this->base . $pattern)) { 
			return realpath($this->base);
		}
		for ($i=0; $i <= $this->depth; $i++) {
			if (file_exists($this->base . str_repeat('/../',$i) . $pattern)) {
				return realpath($this->base . str_repeat('/../',$i));
			}
		}
		return ($this->testing && $pattern == '/webapp' ? $this->getDir('/src/agavi.php') : false);
	}

	public function main() {
		$pdir = $this->getDir();
		if ($pdir) {
			echo "Project dir: $pdir\n";
			$this->project->setProperty('project.dir', $pdir);
			$this->project->setProperty('webapp.dir', realpath($pdir) . '/webapp');
			$this->project->setProperty('tests.dir', realpath($pdir) . '/tests');
		} else {
			throw new BuildException('Unable to determine the location of the project directory based on: '. $this->base);
		}
	}
}
?>

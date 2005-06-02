<?php
class AgaviTestTask extends Task {
	private $glob = '*';
	private $reporter = 'text';
	private $outfile = '';
	private $exit = false;

	public function setGlob($glob)
	{
		$this->glob = (string) $glob;
	}

	public function setReporter($reporter)
	{
		$this->reporter = (string) $reporter;
	}

	public function setOutfile($outfile)
	{
		$this->outfile = (string) $outfile;
	}

	public function setExit($bool)
	{
		$this->exit = (boolean) $bool;
	}

	public function main()
	{
		if (!@require_once('simpletest/unit_tester.php')) {
			throw new BuildException('Requires SimpleTest');
		}
		if ($this->outfile) {
			if ((file_exists($this->outfile) && !is_writeable($this->outfile)) || (!file_exists($this->outfile) && !touch($this->outfile))) {
				throw new BuildException("Could not open/append to outfile: {$this->outfile}");
			}
		}
		$php = (isset($_ENV['PHP_COMMAND']) ? $_ENV['PHP_COMMAND'] : 'php');
		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('file', '/dev/null', 'a')
		);
		$process = proc_open($php, $descriptorspec, $pipes, getcwd());
		if (!is_resource($process)) {
			throw new BuildException("AgaviTest couldn't proc_open: {$php}", PROJECT_MSG_INFO);
		}
		$testcode = '
<?php
define("AGAVITESTGLOB", "'.$this->glob.'");
define("AGAVITESTREPORTER", "'.$this->reporter.'");
require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");
require_once("simpletest/mock_objects.php");
set_include_path(get_include_path().":src");
set_time_limit(0);

$test = new GroupTest("Agavi Test Suite");

foreach (glob("tests/".AGAVITESTGLOB) as $dir) {
	if (!is_dir($dir)) { continue; }
	$group = &new GroupTest(basename($dir) . " Test Suite");
	$files = glob("{$dir}/*Test*.php");
	if (is_array($files)) {
		foreach ($files as $file) {
			$group->addTestFile($file);
		}
	}
	$test->addTestCase($group);
}

if (strtolower(AGAVITESTREPORTER) == "html")
	$rclass = "HTMLReporter";
else
	$rclass = "TextReporter";
exit($test->run(new $rclass()) ? 0 : 1);
?>
';
		fwrite($pipes[0], $testcode);
		fclose($pipes[0]);

		if ($this->outfile) {
			file_put_contents($this->outfile, stream_get_contents($pipes[1]));
			$this->log("AgaviTest output left in: {$this->outfile}", PROJECT_MSG_INFO);
		} else {
			$this->log(stream_get_contents($pipes[1]), PROJECT_MSG_INFO);
		}
		fclose($pipes[1]);
		$return_value = proc_close($process);
		if ($this->exit && ($return_value !== 0)) {
			throw new BuildException('AgaviTest suite FAILED!');
		}
		$this->log("AgaviTest returned: {$return_value}", PROJECT_MSG_INFO);
	}
}
?>

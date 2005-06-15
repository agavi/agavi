<?php
class AgaviTestTask extends Task {
	private $testdir = 'tests';
	private $base_include = 'src:webapp'; // add ./src (core) and ./webapp (project) to the include path
	private $reporter = 'text';
	private $outfile = '';
	private $exit = false;

	public function setTestdir($dir)
	{
		$this->testdir = (string) $dir;
	}

	public function setBaseInclude($include)
	{
		$this->base_include = (string) $include;
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
		@include_once('simpletest/unit_tester.php');
		if (!class_exists('SimpleTestCase', false)) {
			throw new BuildException("\nRequires SimpleTest be accessible from your include path.\neg: include('simpletest/unit_tester.php');\nyour include path is currently set to: " . get_include_path() . ".\nsee http://sourceforge.net/projects/simpletest");
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
define("AGAVITESTREPORTER",	"' . $this->reporter. '");
define("TESTDIR",						"' . $this->testdir . '");
set_include_path(get_include_path() . ":' . $this->base_include . '");
set_time_limit(0);

require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");
require_once("simpletest/mock_objects.php");

if ( !is_dir(TESTDIR) ) {
	echo "No test dir: " . TESTDIR . "\n";
	exit(1);
}


function isTest($name)
{
	return strpos($name, "Test.") ? true : strpos($name, "Tests.");
}

function isHidden($name)
{
	return ($name[0] == ".");
}

function findTests($path)
{
	$iterator = new RecursiveDirectoryIterator($path);
	$group = new GroupTest(basename($iterator->getPath()) . " Test Suite");
	while ($iterator->valid()) {
		if ($iterator->isDir() && !$iterator->isDot() && !isHidden($iterator->getFilename())) {
			if ($iterator->hasChildren() ) {
				$group->addTestCase( findTests($iterator->getPathname()) );
			}
		} else if ($iterator->isFile() && isTest($iterator->getFilename())) { 
			$group->addTestFile($iterator->getPathname());
		}
		$iterator->next();
	}
	return $group;
}
			  
$test = new GroupTest("Agavi Test Suite");
$test->addTestCase( findTests(TESTDIR) );

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
			$this->log("AgaviTest output written to: {$this->outfile}", PROJECT_MSG_INFO);
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

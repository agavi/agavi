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
 * @package    agavi
 * @subpackage buildtools
 *
 * @author     Mike Vincent <mike@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviTestTask extends Task {
	private $agavidir,
					$testdir = 'tests',
					$reporter = 'text',
					$startpoint,
					$base_include = array('src', 'app'),
					$outfile = '',
					$exit = false;

	public function setAgavidir($dir)
	{
		$this->agavidir = (string) $dir;
	}

	public function setTestdir($dir)
	{
		$this->testdir = (string) $dir;
	}

	public function setStartpoint($dir)
	{
		$this->startpoint = (string) $dir;
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
		if (!empty($outfile)) {
			echo "Testing output will be written to: $outfile\n";
		}
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
		if (!empty($this->outfile)) {
			if (!is_writeable($this->outfile) || !touch($this->outfile)) {
				throw new BuildException("Could not open/append to outfile: {$this->outfile}");
			}
		}
		$php = (isset($_ENV['PHP_COMMAND']) ? $_ENV['PHP_COMMAND'] : 'php');
		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('file', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null'), 'a')
		);
		$pipes = array();
		$process = proc_open($php, $descriptorspec, $pipes, getcwd());
		if (!is_resource($process)) {
			throw new BuildException("AgaviTest couldn't proc_open: {$php}", PROJECT_MSG_INFO);
		}
		$testcode = '
<?php
AgaviConfig::set("core.agavi_dir", "' . $this->agavidir . '"); // where the agavi installation resides
AgaviConfig::set("tests.dir", "' . $this->testdir . '"); // where the main tests dir resides
AgaviConfig::set("tests.reporter", "' . ($this->reporter ? $this->reporter : "text") . '"); // which reporter to use for reporting results
AgaviConfig::set("tests.startpoint", "' . ($this->startpoint ? $this->testdir ."/".$this->startpoint : $this->testdir) . '"); // where to begin looking for tests, relative to TESTSDIR

set_include_path(get_include_path() . "' . PATH_SEPARATOR . join(PATH_SEPARATOR, $this->base_include) . '");
set_time_limit(0);

if ( !is_dir(AgaviConfig::get("tests.dir")) ) {
	echo "Tests directory not found, expected: " . AgaviConfig::get("tests.dir") . "\n";
	exit(1);
}
require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");
require_once("simpletest/mock_objects.php");
@include_once(AgaviConfig::get("tests.dir") . "/test_environment.php"); // we probably defined our app location, etc in here. 
require_once(AgaviConfig::get("core.agavi_dir") . "/buildtools/test_setup.php");
@include_once("simpletest/ui/colortext_reporter.php");
@include_once("buildtools/simpletest/vimreporter.class.php");

function isTest($name)
{
	return (bool) preg_match("/(.*)Test(.*)php/", $name);
}

function isHidden($name)
{
	return ($name{0} == ".");
}

function findTests($path, $title="Agavi")
{
	$name = basename($path);
	if ($name == "sandbox") {
		return new GroupTest("ignored sandbox");
	}
	$iterator = new RecursiveDirectoryIterator($path);
	$group = new GroupTest("$title Test Suite");
	while ($iterator->valid()) {
		if ($iterator->isDir() && !$iterator->isDot() && !isHidden($iterator->getFilename())) {
			if ($iterator->hasChildren()) {
				// pass by reference work-around
				$tests =& findTests($iterator->getPathname(), ucfirst(basename($iterator->getPath())));
				$group->addTestCase($tests);
			}
		} else if ($iterator->isFile() && isTest($iterator->getFilename()) && !isHidden($iterator->getFilename())) { 
			$group->addTestFile($iterator->getPathname());
		}
		$iterator->next();
	}
	return $group;
}
echo "Running tests found starting at ". AgaviConfig::get("tests.startpoint") ."\n";			  
$test = findTests(AgaviConfig::get("tests.startpoint"));
switch (strtolower(AgaviConfig::get("tests.reporter"))) {
	case "vim":
		exit($test->run(new VIMReporter()) ? 0 : 1);
		break;
	case "html":
		exit($test->run(new HTMLReporter()) ? 0 : 1);
		break;
	case "color":
		exit($test->run(new ColorTextReporter()) ? 0 : 1);
		break;
	default:
		exit($test->run(new TextReporter()) ? 0 : 1);
		break;
}
?>';
		fwrite($pipes[0], $testcode);
		fclose($pipes[0]);

		if (!empty($this->outfile)) {
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
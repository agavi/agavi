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
 * Main framework class used for autoloading and initial bootstrapping of the 
 * Agavi testing environment
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
class AgaviTesting
{
	/**
	 * @var       PHP_CodeCoverage_Filter The code coverage filter for our tests.
	 */
	public static $codeCoverageFilter = null;
	
	/**
	 * Get the code coverage filter instance we will use for tests.
	 * When running PHPUnit 3.5, this will return the singleton instance.
	 * When running PHPUnit 3.6, this will return the instance we hold internally;
	 * this same instance will be passed to PHPUnit in AgaviTesting::dispatch().
	 *
	 * @return     PHP_CodeCoverage_Filter The code coverage filter for our tests.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.7
	 */
	public static function getCodeCoverageFilter()
	{
		if(self::$codeCoverageFilter === null) {
			// PHP_CodeCoverage doesn't expose any version info, we'll have to check if there is a static getInstance method
			self::$codeCoverageFilter = method_exists('PHP_CodeCoverage_Filter', 'getInstance') ? PHP_CodeCoverage_Filter::getInstance() : new PHP_CodeCoverage_Filter();
		}
		
		return self::$codeCoverageFilter;
	}

	/**
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     Felix Gilcher <felix.gilcher@exozet.com>
	 * @since      1.0.0
	 */
	public static function bootstrap($environment = null)
	{
		if($environment === null) {
			// no env given? let's read one from testing.environment
			$environment = AgaviConfig::get('testing.environment');
		} elseif(AgaviConfig::has('testing.environment') && AgaviConfig::isReadonly('testing.environment')) {
			// env given, but testing.environment is read-only? then we must use that instead and ignore the given setting
			$environment = AgaviConfig::get('testing.environment');
		}
		
		if($environment === null) {
			// still no env? oh man...
			throw new Exception('You must supply an environment name to AgaviTesting::bootstrap() or set the name of the default environment to be used for testing in the configuration directive "testing.environment".');
		}
		
		// finally set the env to what we're really using now.
		AgaviConfig::set('testing.environment', $environment, true, true);
		
		// bootstrap the framework for autoload, config handlers etc.
		Agavi::bootstrap($environment);
		
		ini_set('include_path', get_include_path().PATH_SEPARATOR.dirname(__DIR__));
		
		$GLOBALS['AGAVI_CONFIG'] = AgaviConfig::toArray();
	}

	/**
	 * Dispatch the test run.
	 *
	 * @param      array An array of arguments configuring PHPUnit behavior.
	 * @param      bool  Whether exit() should be called with an appropriate shell
	 *                   exit status to indicate success or failures/errors.
	 *
	 * @return     PHPUnit_Framework_TestResult The PHPUnit result object.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public static function dispatch($arguments = array(), $exit = true)
	{
		
		$suites = include AgaviConfigCache::checkConfig(AgaviConfig::get('core.testing_dir').'/config/suites.xml');
		$master_suite = new AgaviTestSuite('Master');
		
		if(!empty($arguments['include-suite'])) {
			
			$names = explode(',', $arguments['include-suite']);
			unset($arguments['include-suite']);
			
			foreach($names as $name) {
				if(empty($suites[$name])) {
					throw new InvalidArgumentException(sprintf('Invalid suite name %1$s.', $name));
				}
				
				$master_suite->addTest(self::createSuite($name, $suites[$name]));		
			}
				
		} else {
			$excludes = array();
			if(!empty($arguments['exclude-suite'])) {
				$excludes = explode(',', $arguments['exclude-suite']);
				unset($arguments['exclude-suite']);
			}
			foreach($suites as $name => $suite) {
				if(!in_array($name, $excludes)) {
					$master_suite->addTest(self::createSuite($name, $suite));	
				}
			}
		}
		
		if(version_compare(PHPUnit_Runner_Version::id(), '3.6', '<')) {
			// PHP_CodeCoverage_Filter is a singleton
			$runner = new PHPUnit_TextUI_TestRunner();
		} else {
			// PHP_CodeCoverage_Filter instance must be passed to the test runner
			$runner = new PHPUnit_TextUI_TestRunner(null, self::$codeCoverageFilter);
		}
		$result = $runner->doRun($master_suite, $arguments);
		if($exit) {
			// bai
			exit(self::getExitStatus($result));
		} else {
			// return result so calling code can use it
			return $result;
		}
	}
	
	/**
	 * Compute a shell exit status for the given result.
	 * Behaves like PHPUnit_TextUI_Command.
	 *
	 * @param      PHPUnit_Framework_TestResult The test result object.
	 *
	 * @return     int The shell exit code.
	 */
	public static function getExitStatus(PHPUnit_Framework_TestResult $result)
	{
		if($result->wasSuccessful()) {
			return PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
		} elseif($result->errorCount()) {
			return PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT;
		} else {
			return PHPUnit_TextUI_TestRunner::FAILURE_EXIT;
		}
	}
	
	/**
	 * Initialize a suite from the given instructions and add registered tests.
	 *
	 * @param      string Name of the suite
	 * @param      array  An array containing information about the suite
	 *
	 * @return     AgaviTestSuite The initialized test suite object.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function createSuite($name, array $suite) 
	{
		$base = (null == $suite['base']) ? 'tests' : $suite['base'];
		if(!AgaviToolkit::isPathAbsolute($base)) {
			$base = AgaviConfig::get('core.testing_dir').'/'.$base;
		}
		$s = new $suite['class']($name);
		if(!empty($suite['includes'])) {
			foreach(
				new RecursiveIteratorIterator(
					new AgaviRecursiveDirectoryFilterIterator(
						new RecursiveDirectoryIterator($base), 
						$suite['includes'], 
						$suite['excludes']
					), 
					RecursiveIteratorIterator::CHILD_FIRST
				) as $finfo) {
					
				if($finfo->isFile()) {
					$s->addTestFile($finfo->getPathName());
				}
			}
		}
		foreach($suite['testfiles'] as $file) {
			if(!AgaviToolkit::isPathAbsolute($file)) {
				$file = $base.'/'.$file;
			}
			$s->addTestFile($file);
		}
		return $s;
	}
	
	/**
	 * Handles the commandline arguments passed.
	 * 
	 * @return     array the commandline arguments
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public static function processCommandlineOptions()
	{
		$longOptions = array(
			'configuration=',
			'coverage-html=',
			'coverage-clover=',
			'coverage-source=',
			'coverage-xml=',
			'report=',
			'environment=',
			'help',
			'log-graphviz=',
			'log-json=',
			'log-metrics=',
			'log-pmd=',
			'log-tap=',
			'log-xml=',
			'include-suite=',
			'exclude-suite=',
		);
		
		try {
			$options = PHPUnit_Util_Getopt::getopt(
				$_SERVER['argv'],
				'd:',
				$longOptions
			);
		} catch(RuntimeException $e) {
			PHPUnit_TextUI_TestRunner::showError($e->getMessage());
		}
		
		$arguments = array(); 
		
		foreach($options[0] as $option) {
			switch($option[0]) {
				case '--configuration':
					$arguments['configuration'] = $option[1];
					break;
				
				case '--coverage-clover':
				case '--coverage-xml': 
					if(self::checkCodeCoverageDeps()) {
						$arguments['coverageClover'] = $option[1];
					}
					break;
				
				case '--coverage-source': 
					if(self::checkCodeCoverageDeps()) {
						$arguments['coverageSource'] = $option[1];
					}
					break;
				
				case '--coverage-html':
				case '--report': 
					if(self::checkCodeCoverageDeps()) {
						$arguments['reportDirectory'] = $option[1];
					}
					break;
				
				case '--environment':
					$arguments['environment'] = $option[1];
					break;
				
				case '--help':
					self::showHelp();
					exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
					break;
				
				case '--log-json':
					$arguments['jsonLogfile'] = $option[1];
					break;
				
				case '--log-graphviz':
					if(PHPUnit_Util_Filesystem::fileExistsInIncludePath('Image/GraphViz.php')) {
						$arguments['graphvizLogfile'] = $option[1];
					} else {
						throw new AgaviException('The Image_GraphViz package is not installed.');
					}
					break;
				
				case '--log-tap':
					$arguments['tapLogfile'] = $option[1];
					break;
				
				case '--log-xml':
					$arguments['xmlLogfile'] = $option[1];
				break;
				
				case '--log-pmd':
					if(self::checkCodeCoverageDeps()) {
						$arguments['pmdXML'] = $option[1];
					}
					break;
				
				case '--log-metrics':
					if(self::checkCodeCoverageDeps()) {
						$arguments['metricsXML'] = $option[1];
					}
					break;
				
				case '--include-suite':
					$arguments['include-suite'] = $option[1];
					break;
				
				case '--exclude-suite':
					$arguments['exclude-suite'] = $option[1];
					break;
			}
		}
		
		return $arguments;
	}
	
	/**
	 * Checks whether all dependencies for writing code coverage information
	 * are met. 
	 * 
	 * @return     true if all deps are met
	 * @throws     AgaviExecption if a dependency is missing
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function checkCodeCoverageDeps()
	{
		if(extension_loaded('tokenizer') && extension_loaded('xdebug')) {
			return true;
		} else {
			if(!extension_loaded('tokenizer')) {
				throw new AgaviException('The tokenizer extension is not loaded.');
			} else {
				throw new AgaviException('The Xdebug extension is not loaded.');
			}
		}
		
		return false;
	}
	
	/**
	 * shows the help for the commandline call
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function showHelp()
	{
		PHPUnit_TextUI_TestRunner::printVersionString();

		print <<<EOT
Usage: run-tests.php [switches]

  --environment <envname>  use environment named <envname> to run the tests. Defaults to "testing".

  --log-graphviz <file>    Log test execution in GraphViz markup.
  --log-json <file>        Log test execution in JSON format.
  --log-tap <file>         Log test execution in TAP format to file.
  --log-xml <file>         Log test execution in XML format to file.
  --log-metrics <file>     Write metrics report in XML format.
  --log-pmd <file>         Write violations report in PMD XML format.

  --configuration <file>   PHPUnit XML configuration file to use.

  --coverage-html <dir>    Generate code coverage report in HTML format.
  --coverage-clover <file> Write code coverage data in Clover XML format.
  --coverage-source <dir>  Write code coverage / source data in XML format.

  --include-suite <suites> run only suites named <suite>, accepts a list of suites, comma separated.
  --exclude-suite <suites> run all but suites named <suite>, accepts a list of suites, comma separated.

  --help                   Prints this usage information.


EOT;
	}
}

?>
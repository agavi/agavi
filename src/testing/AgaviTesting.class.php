<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
		
		ini_set('include_path', get_include_path().PATH_SEPARATOR.dirname(dirname(__FILE__)));
		
		$GLOBALS['AGAVI_CONFIG'] = AgaviConfig::toArray();
	}

	public static function dispatch($arguments = array())
	{		
		$GLOBALS['__PHPUNIT_BOOTSTRAP'] = dirname(__FILE__).'/templates/AgaviBootstrap.tpl.php';
		
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
		
		$runner = new PHPUnit_TextUI_TestRunner();
		$runner->doRun($master_suite, $arguments);
	}
	
	protected static function createSuite($name, $suite) 
	{
		$s = new $suite['class']($name);
		foreach($suite['testfiles'] as $file) {
			$s->addTestFile('tests/'.$file);
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
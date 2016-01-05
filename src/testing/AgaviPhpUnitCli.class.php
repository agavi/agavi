<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2014 the Agavi Project.                                |
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
 * Main framework class used for running tests on the command line interface.
 *
 * @package    agavi
 * @subpackage testing
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 */
class AgaviPhpUnitCli extends PHPUnit_TextUI_Command
{
	
	/**
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	public function __construct()
	{
		$this->longOptions['environment='] = 'handleEnvironment';
		$this->longOptions['include-suite='] = 'handleIncludeSuite';
		$this->longOptions['exclude-suite='] = 'handleExcludeSuite';
		$this->longOptions['no-expand-configuration'] = 'handleNoExpandConfiguration';
		
		$this->arguments['agaviEnvironment'] = !empty($_SERVER['AGAVI_ENVIRONMENT']) ? $_SERVER['AGAVI_ENVIRONMENT'] : 'testing';
		$this->arguments['agaviIncludeSuites'] = array();
		$this->arguments['agaviExcludeSuites'] = array();
		$this->arguments['agaviExpandConfiguration'] = true;
	}

	/**
	 * Callback handling the --environment command line option.
	 *
	 * @param      string The Agavi environment name.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function handleEnvironment($value)
	{
		$this->arguments['agaviEnvironment'] = $value;
	}
	
	/**
	 * Callback handling the --include-suite command line option.
	 *
	 * @param      string The suite names, separated by comma, to include.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function handleIncludeSuite($value)
	{
		$this->arguments['agaviIncludeSuites'] = array_merge(
			$this->arguments['agaviIncludeSuites'],
			explode(',', $value)
		);
	}
	
	/**
	 * Callback handling the --exclude-suite command line option.
	 *
	 * @param      string The suite names, separated by comma, to exclude.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function handleExcludeSuite($value)
	{
		$this->arguments['agaviExcludeSuites'] = array_merge(
			$this->arguments['agaviExcludeSuites'],
			explode(',', $value)
		);
	}
	
	/**
	 * Callback handling the --no-expand-configuration command line option.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function handleNoExpandConfiguration()
	{
		$this->arguments['agaviExpandConfiguration'] = false;
	}
	
	
	/**
	 * Dispatch the test run.
	 *
	 * @param      array An array containing the command line arguments
	 * @param      bool  Whether exit() should be called with an appropriate shell
	 *                   exit status to indicate success or failures/errors.
	 *
	 * @return     int   The return process return code (if $exit was false)
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	public static function dispatch($argv, $exit = true) {
		$command = new static();
		return $command->run($argv, $exit);
	}
	
	/**
	 * Show the help message.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function showHelp()
	{
		parent::showHelp();
		echo <<<EOT

Agavi specific arguments:

  --environment <envname>   use environment named <envname> to run the tests.
                            Defaults to "testing".
  --include-suite <suites>  run only suites named <suite>, accepts a list of
                            suites, comma separated.
  --exclude-suite <suites>  run all but suites named <suite>, accepts a list
                            of suites, comma separated.
  --no-expand-configuration Don't expand configuration variables in the 
                            configuration file
 
NOTE:
  Unless --no-expand-configuration is given the configuration file given to
  PHPUnit is generated in Agavi's cache directory. So you can't use relative
  paths in the configuration file. Use  %agavi.app_dir%, %core.testing_dir% or
  something applicable to your case.


EOT;
	}

	/**
	 * Custom callback for test suite discovery.
	 * This is called by PHPUnit in the setup process, right after all command line 
	 * arguments have been parsed.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected function handleCustomTestSuite()
	{
		// ensure the bootstrap script doesn't run and bootstraps agavi another time
		define('AGAVI_TESTING_BOOTSTRAPPED', true);
		AgaviToolkit::clearCache();
		$this->bootstrap($this->arguments['agaviEnvironment']);
		
		
		// use the default configuration only if another configuration was not given as command line argument
		$defaultConfigPath = AgaviConfig::get('core.testing_dir') . '/config/phpunit.xml';
		if(empty($this->arguments['configuration']) && is_file($defaultConfigPath)) {
			$this->arguments['configuration'] = $defaultConfigPath;
		}
		
		$this->arguments['configuration'] = $this->expandConfiguration($this->arguments['configuration']);

		if(count($this->options[1]) > 0) {
			// positional args were given, so the user specified a test or folder on the command line
			return;
		}
		
		$suites = require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.testing_dir') . '/config/suites.xml'));
		
		$masterSuite = new AgaviTestSuite('Master');
		
		if($this->arguments['agaviIncludeSuites']) {
			foreach($this->arguments['agaviIncludeSuites'] as $name) {
				if(empty($suites[$name])) {
					throw new InvalidArgumentException(sprintf('Invalid suite name %1$s.', $name));
				}
				
				$masterSuite->addTest(self::createSuite($name, $suites[$name]));
			}
		} else {
			foreach($suites as $name => $suite) {
				if(!in_array($name, $this->arguments['agaviExcludeSuites'])) {
					$masterSuite->addTest(self::createSuite($name, $suite));
				}
			}
		}
		
		$this->arguments['test'] = $masterSuite;
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
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	protected static function createSuite($name, array $suite)
	{
		$base = (null == $suite['base']) ? 'tests' : $suite['base'];
		if(!AgaviToolkit::isPathAbsolute($base)) {
			$base = AgaviConfig::get('core.testing_dir') . '/' . $base;
		}
		$s = new $suite['class']($name);
		if(!empty($suite['includes'])) {
			$files = iterator_to_array(new RecursiveIteratorIterator(
				new AgaviRecursiveDirectoryFilterIterator(
					new RecursiveDirectoryIterator($base),
					$suite['includes'],
					$suite['excludes']
				),
				RecursiveIteratorIterator::CHILD_FIRST
			));
			// ensure that the execution order of the tests is always in deterministic
			// order and doesn't depend on the filesystem order
			usort($files, function($a, $b) {
				return strcmp($a->getPathName(), $b->getPathName());
			});
			
			foreach($files as $finfo) {
				if($finfo->isFile()) {
					$s->addTestFile($finfo->getPathName());
				}
			}
		}
		foreach($suite['testfiles'] as $file) {
			if(!AgaviToolkit::isPathAbsolute($file)) {
				$file = $base . '/' . $file;
			}
			$s->addTestFile($file);
		}
		return $s;
	}
	
	/**
	 * Runs AgaviToolkit::expandDirectives() on all attributes and text nodes of
	 * the given file and writes a it to a new file in the Agavi cache directory.
	 *
	 * @param      string The path to the xml file
	 * 
	 * @return     string The path to the expanded file
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
	 */
	private static function expandConfiguration($file) {
		// file does not exist, let PHPUnit handle that case
		if(!is_readable($file) || !is_file($file)) {
			return $file;
		}
		
		$doc = new DOMDocument();
		$doc->substituteEntities = true;
		$doc->load($file);
		$xpath = new DOMXPath($doc);
		$attributeNodes = $xpath->query('//@*');
		foreach($attributeNodes as $attributeNode) {
			$attributeNode->value = AgaviToolkit::expandDirectives($attributeNode->value);
		}
		$textNodes = $xpath->query('//text()');
		foreach($textNodes as $textNode) {
			$textNode->nodeValue = AgaviToolkit::expandDirectives($textNode->nodeValue);
		}
		
		$translatedFile = AgaviConfigCache::getCacheName($file);
		AgaviConfigCache::writeCacheFile($file, $translatedFile, $doc->saveXML());
		return $translatedFile;
	}
	
	/**
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     Felix Gilcher <felix.gilcher@exozet.com>
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.1.0
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
}

?>
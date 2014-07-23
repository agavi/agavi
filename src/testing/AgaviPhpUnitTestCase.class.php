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
 * AgaviPhpUnitTestCase is the base class for all Agavi Testcases.
 * 
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
abstract class AgaviPhpUnitTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var        string  the name of the environment to bootstrap in isolated tests.
	 */
	protected $isolationEnvironment;
	
	/**
	 * @var        string  the name of the default context to use in isolated tests.
	 */
	protected $isolationDefaultContext;
	
	/**
	 * @var         bool if the cache in the isolated process should be cleared
	 */
	protected $clearIsolationCache = false;
	
	/**
	 * @var         string store the dataName since we can't access it from PHPUnit_Framework_TestCase.
	 */
	protected $myDataName;
	
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param        string
	 * @param        array
	 * @param        string
	 * 
	 * @since        1.1.0
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->myDataName = $dataName;
	}
	
	
	/**
	 * set the environment to bootstrap in isolated tests
	 * 
	 * @param        string the name of the environment
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.0
	 */
	public function setIsolationEnvironment($environmentName)
	{
		$this->isolationEnvironment = $environmentName;
	}
	
	
	/**
	 * get the environment to bootstrap in isolated tests
	 * 
	 * @return       string the name of the isolation environment
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getIsolationEnvironment()
	{
		$environmentName = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviIsolationEnvironment'])) {
			$environmentName = $annotations['method']['agaviIsolationEnvironment'][0];
		} elseif(!empty($annotations['class']['agaviIsolationEnvironment'])) {
			$environmentName = $annotations['class']['agaviIsolationEnvironment'][0];
		} elseif(!empty($this->isolationEnvironment)) {
			$environmentName = $this->isolationEnvironment;
		}
		
		return $environmentName;
	}
	
	
	/**
	 * set the default context to use in isolated tests
	 * 
	 * @param        string the name of the context
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function setIsolationDefaultContext($contextName)
	{
		$this->isolationDefaultContext = $contextName;
	}
	
	
	/**
	 * get the default context to use in isolated tests
	 * 
	 * @return       string the default context to use in isolated tests
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getIsolationDefaultContext()
	{
		$ctxName = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviIsolationDefaultContext'])) {
			$ctxName = $annotations['method']['agaviIsolationDefaultContext'][0];
		} elseif(!empty($annotations['class']['agaviIsolationDefaultContext'])) {
			$ctxName = $annotations['class']['agaviIsolationDefaultContext'][0];
		} elseif(!empty($this->isolationDefaultContext)) {
			$ctxName = $this->isolationDefaultContext;
		}
		
		return $ctxName;
	}
	
	
	/**
	 * set whether the cache should be cleared for the isolated subprocess
	 * 
	 * @param        bool true if the cache should be cleared
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function setClearCache($flag)
	{
		$this->clearIsolationCache = (bool)$flag;
	}
	
	
	/**
	 * check whether to clear the cache in isolated tests
	 * 
	 * @return       bool true if the cache is cleared in isolated tests
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getClearCache()
	{
		$flag = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviClearIsolationCache'])) {
			$flag = true;
		} elseif(!empty($annotations['class']['agaviClearIsolationCache'])) {
			$flag = true;
		} else {
			$flag = $this->clearIsolationCache;
		}
		
		return $flag;
	}
	
	/**
	 * Retrieve the classes and defining files the given class depends on (including the given class)
	 *
	 * @param        ReflectionClass The class to get the dependend classes for.
	 * @param        callable A callback function which takes a file name as argument
	 *                        and returns whether the file is blacklisted.
	 *
	 * @return       string[] An array containing class names as keys and path to the 
	 *                        file's defining class as value.
	 *
	 * @author       Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since        1.1.0
	 */
	private function getClassDependendFiles(ReflectionClass $reflectionClass, $isBlacklisted) {
		$requires = array();
		
		while($reflectionClass) {
			$file = $reflectionClass->getFileName();
			// we don't care for duplicates since we're using require_once anyways
			if(!$isBlacklisted($file) && is_file($file)) {
				$requires[$reflectionClass->getName()] = $file;
			}
			foreach($reflectionClass->getInterfaces() as $interface) {
				$file = $interface->getFileName();
				$requires = array_merge($requires, $this->getClassDependendFiles($interface, $isBlacklisted));
			}
			if(is_callable(array($reflectionClass, 'getTraits'))) {
				// FIXME: remove check after bumping php requirement to 5.4
				foreach($reflectionClass->getTraits() as $trait) {
					$file = $trait->getFileName();
					$requires = array_merge($requires, $this->getClassDependendFiles($trait, $isBlacklisted));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		return $requires;
	}
	
	/**
	 * Get the dependend classes of this test.
	 *
	 * @return       string[] An array containing class names as keys and path to the 
	 *                        file's defining class as value.
	 *
	 * @author       Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since        1.1.0
	 */
	private function getDependendClasses() {
		// We need to collect the dependend classes in case there is a test which 
		// has set @agaviBootstrap to off. That results in the Agavi autoloader not
		// being started and if the test class depends on any files from Agavi (like
		// AgaviPhpUnitTestCase) it would not be loaded when the test is instantiated
		
		$classesInTest = array();
		$reflectionClass = new ReflectionClass(get_class($this));
		$testFile = $reflectionClass->getFileName();
		
		$getDeclaredFuncs = array('get_declared_classes', 'get_declared_interfaces');
		if(version_compare(PHP_VERSION, '5.4', '>=')) {
			$getDeclaredFuncs[] = 'get_declared_traits';
		}
		foreach($getDeclaredFuncs as $getDeclaredFunc) {
			foreach($getDeclaredFunc() as $name) {
				$reflectionClass = new ReflectionClass($name);
				if($testFile === $reflectionClass->getFileName()) {
					$classesInTest[] = $name;
				}
			}
		}
		
		// FIXME: added by phpunit 4.x
		if(class_exists('PHPUnit_Util_Blacklist')) {
			$blacklist = new PHPUnit_Util_Blacklist;
			$isBlacklisted = function($file) use ($testFile, $blacklist) {
				return $file === $testFile || $blacklist->isBlacklisted($file);
			};
		} elseif(is_callable(array('PHPUnit_Util_GlobalState', 'phpunitFiles'))) {
			$blacklist = PHPUnit_Util_GlobalState::phpunitFiles();
			$isBlacklisted = function($file) use ($testFile, $blacklist) {
				return $file === $testFile || isset($blacklist[$file]);
			};
		}

		$classesToFile = array('AgaviTesting' => realpath(__DIR__ . '/AgaviTesting.class.php'));
		foreach($classesInTest as $className) {
			$classesToFile = array_merge(
				$classesToFile,
				$this->getClassDependendFiles(new ReflectionClass($className), $isBlacklisted)
			);
		}
		
		return $classesToFile;
	}
	
	/**
	 * Performs custom preparations on the process isolation template.
	 *
	 * @param        Text_Template $template
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.2
	*/
	protected function prepareTemplate(Text_Template $template)
	{
		parent::prepareTemplate($template);
		
		// FIXME: workaround for php unit bug (https://github.com/sebastianbergmann/phpunit/pull/1338)
		$template->setVar(array(
			'dataName' => "'.(" . var_export($this->myDataName, true) . ").'"
		));

		// FIXME: if we have full composer autoloading we can remove this
		// we need to restore the included files even without global state, since otherwise
		// the agavi test class files would be missing.
		// We can't write include()s directly since Agavi possibly get's bootstrapped later
		// in the process (but before the test instance is created) and if we'd load any
		// files which are being loaded by the bootstrap process chaos would ensue since
		// the bootstrap process uses plain include()s without _once
		$fileAutoloader = sprintf('
			spl_autoload_register(function($name) {
				$classMap = %s;
				if(isset($classMap[$name])) {
					include($classMap[$name]);
				}
			});
		', var_export($this->getDependendClasses(), true));
		
		// these constants are either used by out bootstrap wrapper script 
		// (AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP) or can be used by the user's 
		// bootstrap script (AGAVI_TESTING_IN_SEPERATE_PROCESS)
		$constants = sprintf('
			define("AGAVI_TESTING_IN_SEPERATE_PROCESS", true);
			define("AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP", %s);
			',
			var_export(isset($GLOBALS["__PHPUNIT_BOOTSTRAP"]) ? $GLOBALS["__PHPUNIT_BOOTSTRAP"] : null, true)
		);
		
		
		$isolatedTestSettings = array(
			'environment' => $this->getIsolationEnvironment(),
			'defaultContext' => $this->getIsolationDefaultContext(),
			'clearCache' => $this->getClearCache(),
			'bootstrap' => $this->doBootstrap(),
		);
		$globals = sprintf('
			$GLOBALS["AGAVI_TESTING_CONFIG"] = %s;
			$GLOBALS["AGAVI_TESTING_ISOLATED_TEST_SETTINGS"] = %s;
			$GLOBALS["__PHPUNIT_BOOTSTRAP"] = %s;
			',
			var_export(AgaviConfig::toArray(), true),
			var_export($isolatedTestSettings, true),
			var_export(__DIR__ . '/scripts/IsolatedBootstrap.php', true)
		);

		if(!$this->preserveGlobalState) {
			$template->setVar(array(
				'included_files' => $fileAutoloader,
				'constants' => $constants,
				'globals' => $globals,
			));
		} else {
			// HACK: oh great, text/template doesn't expose the already set variables, but we need to modify
			// them instead of overwriting them. So let's use the reflection to the rescue here.
			$reflected = new ReflectionObject($template);
			$property = $reflected->getProperty('values');
			$property->setAccessible(true);
			$oldVars = $property->getValue($template);
			$template->setVar(array(
				'included_files' => $fileAutoloader,
				'constants' => $oldVars['constants'] . PHP_EOL . $constants,
				'globals' => $oldVars['globals'] . PHP_EOL . $globals,
			));
			
		}
	}
	
	/**
	 * Whether or not an agavi bootstrap should be done in isolation.
	 * 
	 * @return       boolean true if agavi should be bootstrapped
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	protected function doBootstrap()
	{
		$flag = true;
			
		$annotations = $this->getAnnotations();
		if(!empty($annotations['method']['agaviBootstrap'])) {
			$flag = AgaviToolkit::literalize($annotations['method']['agaviBootstrap'][0]);
		} elseif(!empty($annotations['class']['agaviBootstrap'])) {
			$flag = AgaviToolkit::literalize($annotations['class']['agaviBootstrap'][0]);
		}
		return $flag;
	}
	
}

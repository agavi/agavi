<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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

	public static function dispatch()
	{		
		$arguments = self::handleArguments(); // we need to parse the arguments here as we reset $_SERVER somewhere down the line.
		
		$GLOBALS['__PHPUNIT_BOOTSTRAP'] = dirname(__FILE__).'/templates/AgaviBootstrap.tpl.php';

		$suites = include AgaviConfigCache::checkConfig(AgaviConfig::get('core.app_dir').'/../test/config/suites.xml');
		$master_suite = new AgaviTestSuite('Master');
		foreach ($suites as $name => $suite)
		{
			$s = new $suite['class']($name);
			foreach ($suite['testfiles'] as $file)
			{
				$s->addTestFile('tests/'.$file);
			}
			$master_suite->addTest($s);
		}
		
		$runner = new PHPUnit_TextUI_TestRunner();
		$runner->doRun($master_suite, $arguments);
	}
	
	protected static function handleArguments()
	{
		$longOptions = array(
			'coverage-html=',
			'coverage-clover=',
			'coverage-source=',
			'coverage-xml=',
			'report',
		);
		
		try {
			$options = PHPUnit_Util_Getopt::getopt(
				$_SERVER['argv'],
				'd:',
				$longOptions
			);
		} catch (RuntimeException $e) {
			PHPUnit_TextUI_TestRunner::showError($e->getMessage());
		}
		
		$arguments = array(); 
		
		foreach ($options[0] as $option) {
			switch ($option[0]) {
				case '--coverage-clover':
				case '--coverage-xml': {
					if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
						$arguments['coverageClover'] = $option[1];
					} else {
						if (!extension_loaded('tokenizer')) {
							self::showMissingDependency('The tokenizer extension is not loaded.');
						} else {
							self::showMissingDependency('The Xdebug extension is not loaded.');
						}
					}
				}
				break;

				case '--coverage-source': {
					if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
						$arguments['coverageSource'] = $option[1];
					} else {
						if (!extension_loaded('tokenizer')) {
							self::showMissingDependency('The tokenizer extension is not loaded.');
						} else {
							self::showMissingDependency('The Xdebug extension is not loaded.');
						}
					}
				}
				break;

				case '--coverage-html':
				case '--report': {
					if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
						$arguments['reportDirectory'] = $option[1];
					} else {
						if (!extension_loaded('tokenizer')) {
							self::showMissingDependency('The tokenizer extension is not loaded.');
						} else {
							self::showMissingDependency('The Xdebug extension is not loaded.');
						}
					}
				}
				break;
			}
		}
		
		return $arguments;
	}
	
	/**
     * @param string $message
     */
    public static function showMissingDependency($message)
    {
        PHPUnit_TextUI_TestRunner::printVersionString();
        print $message . "\n";
        exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
    }
}

?>
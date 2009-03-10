<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
	 * Runs the test case and collects the results in a TestResult object.
	 * If no TestResult object is passed a new one will be created.
	 *
	 * @param  PHPUnit_Framework_TestResult $result
	 * @return PHPUnit_Framework_TestResult
	 * @throws InvalidArgumentException
	 */
	public function run(PHPUnit_Framework_TestResult $result = NULL)
	{
		
		if(!empty($this->isolationEnvironment)) {
			$GLOBALS['test.isolationEnvironment'] = $this->isolationEnvironment;
		}
		
		$result = parent::run($result);
		
		// restore the testing environment
		$GLOBALS['test.isolationEnvironment'] = null;
		
		return $result;
	}
	
	/**
	 * set the environment to bootstrap in isolated tests
	 * 
	 * @param        string the name of the environment
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since      1.0.0
	 */
	public function setIsolationEnvironment($environmentName)
	{
		$this->isolationEnvironment = $environmentName;
	}
}
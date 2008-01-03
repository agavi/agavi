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
 * @package    agavi
 * @subpackage tests2
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */

class AgaviTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @param  mixed   $object
	 * @return string
	 * @access private
	 * @static
	 */
	private static function objectToString($object) {
		if(is_array($object) || is_object($object)) {
			$object = serialize($object);
		}

		return $object;
	}
	protected function doAssertReference(&$expected, &$actual)
	{
		$ret = true;
		if(!is_object($actual)) {
			$tmp = $actual;
			$actual = uniqid('reftest');
		}

		if($expected !== $actual) {
			$ret = false;
		}

		if(isset($tmp)) {
			$actual = $tmp;
		}

		return $ret;
	}

	public function assertReference(&$expected, &$actual, $message = '')
	{
		if(!$this->doAssertReference($expected, $actual)) {
			self::fail(sprintf('%s%sexpected reference: %s was not a reference of: %s', 
				$message,
				$message != '' ? ' ' : '',
				self::objectToString($expected),
				self::objectToString(isset($tmp) ? $actual = $tmp : $actual)
			));
		}
	}

	public function assertCopy(&$expected, &$actual, $message = '')
	{
		try
		{
			$this->assertEquals($expected, $actual);
			if($this->doAssertReference($expected, $actual))
			{
				self::fail(sprintf('%s%sexpected copy: %s was a reference of: %s', 
					$message,
					$message != '' ? ' ' : '',
					self::objectToString($expected),
					self::objectToString(isset($tmp) ? $actual = $tmp : $actual)
				));
			}
		}
		catch(PHPUnit_Framework_AssertionFailedError $e)
		{
			self::fail(sprintf('%s%sexpected copy: %s was not a copy of: %s', 
				$message,
				$message != '' ? ' ' : '',
				self::objectToString($expected),
				self::objectToString(isset($tmp) ? $actual = $tmp : $actual)
			));
		}
		
	}
}

?>
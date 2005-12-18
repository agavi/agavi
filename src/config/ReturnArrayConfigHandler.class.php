<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 Agavi Foundation                                  |
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
 * ReturnArrayConfigHandler allows you to retrieve the contents of a config
 * file as an array
 *
 * @package    agavi
 * @subpackage config
 *
 * @author    David Zuelke (dz@bitxtender.com) {@link http://www.agavi.org}
 * @copyright (c) authors
 * @since     0.10.0
 * @version   $Id$
 */

class ReturnArrayConfigHandler extends IniConfigHandler
{
	/**
	 * @see IniConfigHandler::execute()
	 * @author David Zuelke (dz@bitxtender.com)
	 * @since  0.10.0
	 */
	public function &execute($config)
	{
		$real_booleans = (in_array($this->getParameter('real_booleans', false), array('false', 'off', 'no')));
		$ini = $this->parseIni($config);
		if(count($ini) != count($ini, COUNT_RECURSIVE))
		{
			foreach($ini as $section => $values)
			{
				$ini[$section] = self::addDimensions($values, $real_booleans);
			}
		}
		else
		{
			$ini = self::addDimensions($ini, $real_booleans);
		}
		$return = "<?php return " . var_export($ini, true) . ";?>";
		return $return;
	}

	/**
	 * Helper method to convert keys.like.these in ini files to a multi-
	 * dimensional array
	 * 
	 * @param array The one-dimensional input array
	 * @param bool Convert boolean strings to literal boolean values
	 * @return array The transformed version of the input array
	 * @author David Zuelke (dz@bitxtender.com)
	 * @since  0.10.0
	 */
	public static function addDimensions($input, $real_booleans = false)
	{
		$output = array();
		foreach($input as $key => $value)
		{
			if($real_booleans) {
				$value = self::real_booleans($value);
			}
			$parts = explode('.', $key);
			$ref =& $output;
			$count = count($parts);
			for($i = 0; $i < $count; $i++) {
				$partKey = $parts[$i];
				if(($i + 1) == $count) {
					$ref[$partKey] = $value;
				} else {
					$ref =& $ref[$partKey];
				}
			}
		}
		return $output;
	}

	public static function real_booleans($value) 
	{
		$bool_false = array('false', 'off', 'no');
		$bool_true = array('true', 'on', 'yes');
		if (in_array(strtolower($value), $bool_false)) {
			return false;
		} else if (in_array(strtolower($value), $bool_true)) {
			return true;
		}
		return $value;
	}
	
}
?>
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
 * @author    David Zülke (dz@bitxtender.com) {@link http://www.agavi.org}
 * @copyright (c) authors
 * @since     0.10.0
 * @version   $Id$
 */

class ReturnArrayConfigHandler extends IniConfigHandler
{
	/**
	 * @see IniConfigHandler::execute()
	 * @author David Zülke (dz@bitxtender.com)
	 * @since  0.10.0
	 */
	public function &execute($config)
	{
		$ini = $this->parseIni($config);
		if(count($ini) != count($ini, COUNT_RECURSIVE))
		{
			foreach($ini as $section => $values)
			{
				$ini[$section] = self::addDimensions($values);
			}
		}
		else
		{
			$ini = self::addDimensions($ini);
		}
		$return = "<?php return " . var_export($ini, true) . ";?>";
		return $return;
	}

	/**
	 * Helper method to convert keys.like.these in ini files to a multi-
	 * dimensional array
	 * 
	 * @param array The one-dimensional input array
	 * @return array The transformed version of the input array
	 * @author David Zülke (dz@bitxtender.com)
	 * @since  0.10.0
	 */
	public static function addDimensions($input)
	{
		$output = array();
		foreach($input as $key => $value)
		{
			// param.something = sompn         ; $array['param'] = array('something' => 'sompn');
			// key.param.something = sompnToo  ; $array['key.param'] = array('something' => 'sompnToo');
			// so basically, take the chunk following the last dot as the new baby key (something), everything prior is the parent key (param/key.param)
			// think that even works out for the numeric indexed arrays
			$lastDot = strrpos($key, '.');
			if ($lastDot !== false) {
				$parentKey = substr($key, 0, $lastDot);
				$childKey = substr($key, $lastDot + 1);
				$output[$parentKey] = isset($output[$parentKey]) ? array_merge($output[$parentKey], array($childKey => $value)) : array($childKey => $value);
			} else {
				$output[$key] = $value;
			}
		}
		return $output;
	}
}

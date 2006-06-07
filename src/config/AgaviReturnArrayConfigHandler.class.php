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
 * AgaviReturnArrayConfigHandler allows you to retrieve the contents of a config
 * file as an array
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */

class AgaviReturnArrayConfigHandler extends AgaviConfigHandler
{
	/**
	 * @see        AgaviIniConfigHandler::execute()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function execute($config, $context = null)
	{
		$configurations = AgaviConfigCache::parseConfig($config, false);

		$data = array();
		$env = AgaviConfig::get('core.environment');
		foreach($configurations as $cfg) {
			if(($cfg->hasAttribute('environment') && $cfg->getAttribute('environment') != $env) || ($context !== null && $cfg->hasAttribute('context') && $cfg->getAttribute('context') != $context))
				continue;

			$data = $this->convertToArray($cfg);
		}

		$return = "<?php return " . var_export($data, true) . ";?>";
		return $return;

	}

	protected function convertToArray($item, $append = false)
	{
		$data = array();

		if(!$item->hasChildren()) {
			$data = $item->getValue();
		} else {
			foreach($item->getChildren() as $key => $child) {
				if(is_int($key) && !$child->hasAttribute('name')) {
					$data[] = $this->convertToArray($child);
				} else {
					$name = $child->hasAttribute('name') ? $child->getAttribute('name') : $child->getName();
					$data[$name] = $this->convertToArray($child);
				}
			}
		}

		foreach($item->getAttributes() as $name => $value) {
			if(!isset($data[$name])) {
				$data[$name] = $this->literalize($value);
			}
		}
		return $data;
	}

	/**
	 * Helper method to convert keys.like.these in ini files to a multi-
	 * dimensional array
	 * 
	 * @param      array The one-dimensional input array
	 * @param      bool Convert boolean strings to literal boolean values
	 *
	 * @return     array The transformed version of the input array
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
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
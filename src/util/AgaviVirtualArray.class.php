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
 * AgaviPath implements handling of arrays with virtual paths
 * 
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviVirtualArray
{
	/**
	 * @var        array array data
	 */
	protected $data = array();
	
	/**
	 * constructor
	 * 
	 * @param      string path to be handled by the object
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Returns the stored data array
	 * 
	 * @return     array The data array
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getData()
	{
		return $this->data;
	}

	public function hasValue($path)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		return AgaviArrayPathDefinition::hasValue($parts['parts'], $this->data);
	}

	public function &getValue($path, $default = null)
	{
		if(isset($this->data[$path])) {
			return $this->data[$path];
		}
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		return AgaviArrayPathDefinition::getValueFromArray($parts['parts'], $this->data, $default);
	}

	public function setValue($path, &$value)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		AgaviArrayPathDefinition::setValueFromArray($parts['parts'], $this->data, $value);
	}

	public function setValues($values)
	{
		$this->data = array_merge($this->data, $values);
	}

	public function &unsetValue($path)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($path);
		return AgaviArrayPathDefinition::unsetValue($parts['parts'], $this->data);
	}

}
?>
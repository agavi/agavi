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

require_once(dirname(__FILE__) . '/AgaviPropertiesType.php');
require_once('phing/system/util/Properties.php');

/**
 * Represents a property reference.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviPropertyType extends AgaviPropertiesType
{
	protected $name = null;
	protected $value = null;
	
	/**
	 * Sets the name of the property.
	 *
	 * @param      string The name of the property.
	 */
	public function setName($name)
	{
		$this->name = (string)$name;
	}
	
	/**
	 * Sets the value of the property.
	 *
	 * @param      mixed The property value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Retrieves the new property or properties.
	 *
	 * @return     Properties The list of properties.
	 */
	public function resolve()
	{
		if($this->name === null) {
			throw new BuildException('The name attribute must be specified');
		}
		if($this->value === null) {
			throw new BuildException('The value attribute must be specified');
		}
		
		$properties = new Properties();
		$properties->setProperty($this->name, $this->value);
		return $properties;
	}
}

?>
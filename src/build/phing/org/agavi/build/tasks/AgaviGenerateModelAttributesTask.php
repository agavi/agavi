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

require_once(dirname(__FILE__) . '/AgaviTask.php');

/**
 * Creates the methods to handle output types for an agavi view.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviGenerateModelAttributesTask extends AgaviTask
{
	
	protected $attributeListProperty = null;
	protected $attributeAccessorsProperty = null;
	protected $name = null;
	protected $type = null;
	protected $accessLevel = null;
	protected $attributeTemplate = null;
	protected $attributeSetterTemplate = null;
	protected $attributeGetterTemplate = null;
	
	/**
	 * Sets the property that this task will modify.
	 *
	 * @param      string The property to modify.
	 */
	public function setAttributeListProperty($property)
	{
		$this->attributeListProperty = $property;
	}
	
	public function setAttributeAccessorsProperty($property)
	{
		$this->attributeAccessorsProperty = $property;
	}

	public function setAttributeName($name)
	{
		$this->name = $name;
	}

	public function setAttributeType($type)
	{
		$this->type = $type;
	}
	
	public function setAttributeAccessLevel($level)
	{
		$this->accessLevel = $level;
	}
	
	public function setAttributeTemplate($path)
	{
		$this->attributeTemplate = $path;
	}
	
	public function setAttributeSetterTemplate($path)
	{
		$this->attributeSetterTemplate = $path;
	}
	
	public function setAttributeGetterTemplate($path)
	{
		$this->attributeGetterTemplate = $path;
	}

	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->attributeListProperty === null) {
			throw new BuildException('The attributeListProperty attribute must be specified');
		}
		
		if($this->attributeAccessorsProperty === null) {
			throw new BuildException('The attributeAccessorsProperty attribute must be specified');
		}
		
		$attrAccessors = $this->project->getUserProperty($this->attributeAccessorsProperty);
		$attrList = $this->project->getUserProperty($this->attributeListProperty);
		
		
		$attributeListItemTemplate = file_get_contents($this->attributeTemplate);
		$attributeSetterTemplate = file_get_contents($this->attributeSetterTemplate);
		$attributeGetterTemplate = file_get_contents($this->attributeSetterTemplate);
		
		$varname = $this->name;
		$variable = '$'.$varname;
		$type = $this->type;
		$level = $this->accessLevel;
		
		$search = array('%%TYPE%%', '%%VARIABLE%%', '%%VARNAME%%', '%%ACCESS_LEVEL%%');
		$replace = array($type, $variable, $varname, $level);
		
		$attrList .= str_replace($search, $replace, $attributeListItemTemplate);
		
		$search[] = '%%METHODNAME%%';
		
		$attrAccessors .= str_replace($search, array_merge($replace, array('set'.ucfirst($varname))), $attributeSetterTemplate);
		$attrAccessors .= str_replace($search, array_merge($replace, array('get'.ucfirst($varname))), $attributeGetterTemplate);
		
		$this->project->setUserProperty($this->attributeAccessorsProperty, $attrAccessors);
		$this->project->setUserProperty($this->attributeListProperty, $attrList);
	}
}

?>
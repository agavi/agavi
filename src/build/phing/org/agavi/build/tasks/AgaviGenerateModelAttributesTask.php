<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

require_once(__DIR__ . '/AgaviTask.php');

/**
 * Creates the code to handle attributes in an agavi model.
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
	/**
	 * @var          string the property to modify for attribute defintions
	 */
	protected $attributeListProperty = null;
	
	/**
	 * @var          string the property to modify for attribute accessors
	 */
	protected $attributeAccessorsProperty = null;
	
	/**
	 * @var          string attributes name
	 */
	protected $name = null;
	
	/**
	 * @var          string attributes type
	 */
	protected $type = null;
	
	/**
	 * @var          string attributes access level
	 */
	protected $accessLevel = null;
	
	/**
	 * @var          string the template to use for the attribute definition
	 */
	protected $attributeTemplate = null;
	
	/**
	 * @var          string the template to use for the attribute setter
	 */
	protected $attributeSetterTemplate = null;
	
	/**
	 * @var          string the template to use for the attribute getter
	 */
	protected $attributeGetterTemplate = null;
	
	/**
	 * Sets the property that this task will use to access the attribute list
	 * code.
	 *
	 * @param        string The property to modify.
	 */
	public function setAttributeListProperty($property)
	{
		$this->attributeListProperty = $property;
	}
	
	/**
	 * Sets the property that this task will use to access the attribute
	 * accessor code.
	 *
	 * @param        string The property to modify.
	 */
	public function setAttributeAccessorsProperty($property)
	{
		$this->attributeAccessorsProperty = $property;
	}

	/**
	 * Sets the attribute name to generate code for.
	 *
	 * @param        string The attribute name.
	 */
	public function setAttributeName($name)
	{
		$this->name = $name;
	}

	/**
	 * Sets the attribute type.
	 *
	 * @param        string The attribute type.
	 */
	public function setAttributeType($type)
	{
		$this->type = $type;
	}
	
	
	/**
	 * Sets the attribute's access level.
	 *
	 * @param        string The attribute type.
	 */
	public function setAttributeAccessLevel($level)
	{
		$level = strtolower($level);
		if(!in_array($level, array("private", "protected", "public"))) {
			throw new BuildException(
				sprintf(
					'The access level "%1$s" is not a valid access level, must be any of [private, protected, public]', 
					$level
				)
			);
		}
		
		$this->accessLevel = $level;
	}
	
	/**
	 * Set the template to use for the attribute-declaration.
	 * 
	 * @param        string the full path to the template
	 */
	public function setAttributeTemplate($path)
	{
		$this->attributeTemplate = $path;
	}
	
	/**
	 * Set the template to use for the attribute-getter-declaration.
	 * 
	 * @param        string the full path to the template
	 */
	public function setAttributeSetterTemplate($path)
	{
		$this->attributeSetterTemplate = $path;
	}
	
	/**
	 * Set the template to use for the attribute-setter-declaration.
	 * 
	 * @param        string the full path to the template
	 */
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
		
		if($this->attributeTemplate === null || !is_readable($this->attributeTemplate)) {
			throw new BuildException(
				sprintf(
					'The attributeTemplate attribute must be specified and must point to a readable template file. Current value is "%1$s".',
					$this->attributeTemplate
				)
			);
		}
		
		if($this->attributeSetterTemplate === null || !is_readable($this->attributeSetterTemplate)) {
			throw new BuildException(
				sprintf(
					'The attributeSetterTemplate attribute must be specified and must point to a readable template file. Current value is "%1$s".',
					$this->attributeSetterTemplate
				)
			);
		}
		
		if($this->attributeGetterTemplate === null || !is_readable($this->attributeGetterTemplate)) {
			throw new BuildException(
				sprintf(
					'The attributeGetterTemplate attribute must be specified and must point to a readable template file. Current value is "%1$s".',
					$this->attributeGetterTemplate
				)
			);
		}
		
		$attrAccessors = $this->project->getUserProperty($this->attributeAccessorsProperty);
		$attrList = $this->project->getUserProperty($this->attributeListProperty);
		
		
		$attributeListItemTemplate = file_get_contents($this->attributeTemplate);
		$attributeSetterTemplate = file_get_contents($this->attributeSetterTemplate);
		$attributeGetterTemplate = file_get_contents($this->attributeGetterTemplate);
		
		
		$type = $this->type;
		$isScalar = in_array(strtolower($type), array("int", "integer", "float", "double", "string", "bool", "boolean", "mixed"));
		if($isScalar) {
			$typehint = "";
		} else {
			$typehint = $type." ";
		}
		$varname = $this->name;
		$variable = '$'.$varname;
		$level = $this->accessLevel;
		
		$search = array('%%TYPE%%', '%%VARIABLE%%', '%%VARNAME%%', '%%ACCESS_LEVEL%%', '%%TYPE_HINT%%');
		$replace = array($type, $variable, $varname, $level, $typehint);
		
		$attrList .= str_replace($search, $replace, $attributeListItemTemplate);
		
		$search[] = '%%METHODNAME%%';
		
		$attrAccessors .= str_replace($search, array_merge($replace, array('set'.ucfirst($varname))), $attributeSetterTemplate);
		$attrAccessors .= str_replace($search, array_merge($replace, array('get'.ucfirst($varname))), $attributeGetterTemplate);
		
		$this->project->setUserProperty($this->attributeAccessorsProperty, $attrAccessors);
		$this->project->setUserProperty($this->attributeListProperty, $attrList);
	}
}

?>
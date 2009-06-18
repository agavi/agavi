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
		
		
		$attributeListItemTemplate = "

	/**
	 * @todo fill in documentation here
	 *
	 * @var          %%TYPE%% 
	 */
	%%ACCESS_LEVEL%% %%VARIABLE%%;
";

		
		$attributeSetterTemplate = "

	/**
	 * Sets the %%VARNAME%% attribute.
	 *
	 * @param        %%TYPE%% the new value for %%VARNAME%%
	 *
	 * @return       void
	 */
	public function %%METHODNAME%%(%%VARIABLE%%)
	{
		\$this->%%VARNAME%% = %%VARIABLE%%;
	}
";

		$attributeGetterTemplate = "

	/**
	 * Retrieves the %%VARNAME%% attribute.
	 *
	 * @return       %%TYPE%% the value for %%VARNAME%%
	 */
	public function %%METHODNAME%%(%%VARIABLE%%)
	{
		return \$this->%%VARNAME%%;
	}
";

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
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
class AgaviGenerateViewMethodsTask extends AgaviTask
{
	/**
	 * @var          string The property to modify.
	 */
	protected $property = null;
	
	/**
	 * @var          string the output type names to generate methods for
	 */
	protected $outputType = array();
	
	/**
	 * @var          string the action name this view belongs to
	 */
	protected $actionName = '';
	
	/**
	 * @var          string the absolute filesytem path to method template
	 */
	protected $methodTemplate = null;
	
	/**
	 * Sets the property that this task will modify.
	 *
	 * @param      string The property to modify.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the output type name to generate code for.
	 *
	 * @param      string the output-type name.
	 */
	public function setOutputType($otName)
	{
		$this->outputType = $otName;
	}
	
	/**
	 * Sets the action's name.
	 *
	 * @param      string the actions name.
	 */
	public function setActionName($name)
	{
		$this->actionName = $name;
	}
	
	/**
	 * Sets the template to use for the output type handling methods.
	 * 
	 * @param        string the absolute filesytem path to method template
	 */
	public function setMethodTemplate($path)
	{
		$this->methodTemplate = $path;
	}

	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		
		if($this->methodTemplate === null || !is_readable($this->methodTemplate)) {
			throw new BuildException(
				sprintf(
					'The methodTemplate attribute must be specified and must point to a readable template file. Current value is "%1$s".',
					$this->methodTemplate
				)
			);
		}
			
		$template = file_get_contents($this->methodTemplate);
		
		$methodDeclarations = $this->project->getUserProperty($this->property);
		
		$methodDeclarations .= str_replace(array('%%OUTPUT_TYPE_NAME%%', '%%ACTION_NAME%%'), array(ucfirst($this->outputType), $this->actionName), $template);
		
		$this->project->setUserProperty($this->property, $methodDeclarations);
	}
}

?>
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
class AgaviGenerateViewMethodsTask extends AgaviTask
{
	
	protected $property = null;
	protected $outputTypes = array();
	protected $actionName = '';
	
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
	 * Sets the output type names to generate code for.
	 *
	 * @param      string a space separated list of output-type names.
	 */
	public function setOutputTypes($otNames)
	{
		if ("" == trim($otNames)) {
			$this->outputTypes = array();
		} else {
			$this->outputTypes = explode(" ", $otNames);
		}		
	}
	
	/**
	 * Sets if the action should be simple.
	 *
	 * @param      boolean true if the action is simple.
	 */
	public function setActionName($name)
	{
		$this->actionName = $name;
	}
	

	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		
		$template = "

	/**
	 * Handles the %%OUTPUT_TYPE_NAME%% output type.
	 *
	 * @parameter  AgaviRequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>An AgaviExecutionContainer to forward the execution to or</li>
	 *                     <li>Any other type will be set as the response content.</li>
	 *                   </ul>
	 */
	public function execute%%OUTPUT_TYPE_NAME%%(AgaviRequestDataHolder \$rd)
	{
		\$this->setup%%OUTPUT_TYPE_NAME%%(\$rd);

		\$this->setAttribute('_title', '%%ACTION_NAME%%');
	}

";
		$methodDeclarations = '';
		foreach ($this->outputTypes as $otName) {
			$methodDeclarations .= str_replace(array('%%OUTPUT_TYPE_NAME%%', '%%ACTION_NAME%%'), array(ucfirst($otName), $this->actionName), $template);
		}
		
		$this->project->setUserProperty($this->property, $methodDeclarations);
		return;
	}
}

?>
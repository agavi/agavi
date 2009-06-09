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
 * Creates the methods to handle requests for an agavi action.
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
class AgaviGenerateActionMethodsTask extends AgaviTask
{
	
	protected $property = null;
	protected $methods = '';
	protected $isSimple = false;
	
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
	 * Sets the methods to generate code for.
	 *
	 * @param      string a space separated list of methodnames.
	 */
	public function setMethods($methodNames)
	{
		if ("" == trim($methodNames)) {
			$this->methods = array();
		} else {
			$this->methods = explode(" ", $methodNames);
		}		
	}
	
	/**
	 * Sets if the action should be simple.
	 *
	 * @param      boolean true if the action is simple.
	 */
	public function setSimple($flag)
	{
		$this->isSimple = (bool)$flag;
	}
	

	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		
		if(count($this->methods) > 0 && $this->isSimple) {
			throw new BuildException('An action cannot serve request methods and be simple at the same time.');
		}
		
		$template = "

	/**
	 * Handles the %%METHOD_NAME%% request method.
	 *
	 * @parameter  AgaviRequestDataHolder the (validated) request data
	 *
	 * @return     mixed <ul>
	 *                     <li>A string containing the view name associated
	 *                     with this action; or</li>
	 *                     <li>An array with two indices: the parent module
	 *                     of the view to be executed and the view to be
	 *                     executed.</li>
	 *                   </ul>
	 */
	public function execute%%METHOD_NAME%%(AgaviRequestDataHolder \$rd)
	{
		return 'Success';
	}
";
		$methodDeclarations = '';
		foreach($this->methods as $methodName) {
			$methodDeclarations .= str_replace('%%METHOD_NAME%%', ucfirst($methodName), $template);
		}
		
		if($this->isSimple) {
		
			$methodDeclarations .= "

	/**
	 * Whether or not this action is \"simple\", i.e. doesn't use validation etc.
	 *
	 * @return     bool true, if this action should act in simple mode, or false.
	 *
	 */
	public function isSimple()
	{
		return true;
	}
";
		}
	
		$this->project->setUserProperty($this->property, $methodDeclarations);
		return;
	}
}

?>
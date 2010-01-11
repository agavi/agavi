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

require_once(dirname(__FILE__) . '/AgaviTask.php');

/**
 * Resolves Agavi configuration directives and variables.
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
class AgaviResolveconfigurationTask extends AgaviTask
{
	protected $property;
	protected $string;
	protected $expandDirectives = true;
	protected $variables = array();

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
	 * Sets the string that this task will read.
	 *
	 * @param      string The string to read.
	 */
	public function setString($string)
	{
		$this->string = $string;
	}
	
	/**
	 * Sets whether directives should be expanded as well as variables.
	 *
	 * @param      bool Whether to expand directives in the input string.
	 */
	public function setExpandDirectives($expandDirectives)
	{
		$this->expandDirectives = StringHelper::booleanValue($expandDirectives);
	}
	
	/**
	 * Adds a new variable to this task.
	 *
	 * @return     AgaviVariableType The new variable.
	 */
	public function createVariable()
	{
		$variable = new AgaviVariableType();
		$this->variables[] = $variable;
		return $variable;
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->string === null) {
			throw new BuildException('The string attribute must be specified');
		}
		
		$this->tryLoadAgavi();
		$this->tryBootstrapAgavi();
		
		$assigns = array();
		foreach($this->variables as $variable) {
			$assigns[$variable->getName()] = $variable->getValue();
		}
		
		$result = AgaviToolkit::expandVariables(
			$this->expandDirectives ? AgaviToolkit::expandDirectives($this->string) : $this->string,
			$assigns
		);
		
		$this->project->setUserProperty($this->property, $result);
	}
}

?>
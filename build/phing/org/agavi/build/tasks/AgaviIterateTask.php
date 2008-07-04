<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * Iterates over a list, calling a target.
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
class AgaviIterateTask extends AgaviTask
{
	protected $property = null;
	protected $list = null;
	protected $target = null;
	protected $delimiter = ' ';
	protected $exceptionsFatal = true;
	
	/**
	 * Sets the property that this task will assign.
	 * 
	 * @param      string The property to assign.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the list that this task will iterate.
	 *
	 * @param      string The list to iterate.
	 */
	public function setList($list)
	{
		$this->list = $list;
	}
	
	/**
	 * Sets the target that this task will call.
	 *
	 * @param      string The target name.
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}
	
	/**
	 * Sets the list delimiter character.
	 *
	 * @param      string The delimiter.
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}
	
	/**
	 * Sets whether exceptions are fatal for targets called by this task.
	 *
	 * @param      bool Whether exceptions should be considered fatal.
	 */
	public function setExceptionsFatal($exceptionsFatal)
	{
		$this->exceptionsFatal = StringHelper::booleanValue($exceptionsFatal);
	}
	
	/**
	 * Executes this target.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->list === null) {
			throw new BuildException('The list attribute must be specified');
		}
		if($this->target === null) {
			throw new BuildException('The target attribute must be specified');
		}
		
		$delimiter = preg_quote($this->delimiter, '#');
		$pattern = sprintf('#(?:(?P<unquoted>[^"\'%s].+)|\'(?P<single_quoted>(?:\\\\\'|[^\'])+)\'|"(?P<double_quoted>(?:\\\\"|[^"])+)")(?=%s|$)#U',
			$delimiter, $delimiter);
		
		$matches = array();
		preg_match_all($pattern, $this->list, $matches, PREG_SET_ORDER);
		
		foreach($matches as $match) {
			$iterated;
			
			/* This has everything to do with the order of the regular expression.
			 * Watch it. */
			if(!empty($match['double_quoted'])) {
				$iterated = str_replace('\\"', '"', $match['double_quoted']);
			}
			elseif(!empty($match['single_quoted'])) {
				$iterated = str_replace('\\\'', '\'', $match['single_quoted']);
			}
			else {
				$iterated = $match['unquoted'];
			}
			
			$this->project->setUserProperty($this->property, $iterated);
			
			$task = $this->project->createTask('agavi.execute-target');
			$task->setName($this->target);
			$task->setExceptionsFatal($this->exceptionsFatal);
			$task->init();
			$task->main();
		}
	}
}

?>

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

require_once(dirname(__FILE__) . '/AgaviTask.php');

/**
 * Retrieves the first element of a given list of items.
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
class AgaviCarTask extends AgaviTask
{
	protected $property = null;
	protected $list = null;
	protected $delimiter = ' ';
	
	/**
	 * Sets the property that this task will modify.
	 *
	 * @param      string The property name.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the list from which this task will read.
	 *
	 * @param      string The list.
	 */
	public function setList($list)
	{
		$this->list = $list;
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
	 * Executes this task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		} elseif($this->list === null) {
			throw new BuildException('The list attribute must be specified');
		}
		
		$transform = new AgaviStringtoarrayTransform();
		$transform->setInput($this->list);
		$transform->setDelimiter($this->delimiter);
		
		$this->project->setUserProperty($this->property, reset($transform->transform()));
	}
}

?>
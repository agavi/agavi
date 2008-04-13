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
 * Transforms a string into an identifier suitable for use in PHP. This class
 * only makes a reasonable guess at a decent identifier, and so the real
 * identifier name should generally be user-configurable.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <impl@cynigram.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviTransformstringtoidentifierTask extends AgaviTask
{
	protected $property = null;
	protected $string = null;
	
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
	 * Sets the string to transform.
	 *
	 * @param      string The string to transform.
	 */
	public function setString($string)
	{
		$this->string = $string;
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->string === null || strlen($this->string) === 0) {
			throw new BuildException('The string attribute must be specified and must be non-empty');
		}
		
		$identifier = str_replace(' ', '', preg_replace('#[^A-Za-z0-9\7F-\FF_ ]#', '_', $this->string));
		if(ctype_digit($identifier[0])) {
			$identifier = '_' . $identifier;
		}
		
		$this->project->setUserProperty($this->property, $identifier);
	}
}

?>
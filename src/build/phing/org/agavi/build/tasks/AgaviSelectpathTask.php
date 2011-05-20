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
 * Selects the first available file from a list of paths.
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
class AgaviSelectpathTask extends AgaviTask
{
	const TYPE_FILE = 'file';
	const TYPE_DIRECTORY = 'directory';
	
	protected $property = null;
	protected $path = '';
	protected $type = null;
	protected $froms = array();
	
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
	 * Sets the path to locate.
	 *
	 * @param      string The path to locate.
	 */
	public function setPath($path)
	{
		/* This must be created here to prevent the directory from
		 * becoming automatically converted to an absolute path. */
		$this->path = new PhingFile($path);
	}
	
	/**
	 * Sets the type that the path must have.
	 *
	 * @param      string One of <code>file</code> or <code>directory</code>.
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Adds a new path to the search list.
	 *
	 * @param      PhingFile The path to add.
	 */
	public function createFrom()
	{
		$from = new AgaviFromType();
		$this->froms[] = $from;
		return $from;
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if(count($this->froms) === 0) {
			throw new BuildException('At least one from tag must be specified');
		}
		
		foreach($this->froms as $from) {
			$path = new PhingFile($from->getPath()->getAbsolutePath() . DIRECTORY_SEPARATOR . $this->path->getPath());
			if(
				($this->type === null && file_exists($path->getPath())) ||
				($this->type === self::TYPE_FILE && is_file($path->getPath())) ||
				($this->type === self::TYPE_DIRECTORY && is_dir($path->getPath()))
			) {
				$this->project->setUserProperty($this->property, $path->getPath());
				return;
			}
		}
	}
}

?>
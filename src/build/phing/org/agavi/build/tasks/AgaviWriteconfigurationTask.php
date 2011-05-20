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
 * Writes XPath-defined configuration values to an Agavi configuration file.
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
class AgaviWriteconfigurationTask extends AgaviTask
{
	protected $file = null;
	protected $path = null;
	protected $value = null;
	protected $namespace = null;
	
	/**
	 * Sets the file to modify.
	 *
	 * @param      PhingFile The file to modify.
	 */
	public function setFile(PhingFile $file)
	{
		$this->file = $file;
	}
	
	/**
	 * Sets the XPath path to search for in the file.
	 *
	 * @param      string The search path.
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}
	
	/**
	 * Sets the new value for the configuration element.
	 *
	 * @param      mixed The new value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Sets the document namespace for this task.
	 *
	 * @param      string The document namespace.
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}
	
	/**
	 * Executes this task.
	 */
	public function main()
	{
		if($this->file === null) {
			throw new BuildException('The file attribute must be specified');
		}
		if($this->path === null) {
			throw new BuildException('The path attribute must be specified');
		}
		if($this->value === null) {
			throw new BuildException('The value attribute must be specified');
		}
		
		$document = new DOMDocument();
		$document->preserveWhiteSpace = true;
		$document->load($this->file->getAbsolutePath());
		
		$path = new DOMXPath($document);
		$path->registerNamespace('envelope', 'http://agavi.org/agavi/config/global/envelope/1.0');
		if($this->namespace !== null) {
			$path->registerNamespace('document', $this->namespace);
		}
		
		$entries = $path->query($this->path);
		foreach($entries as $entry) {
			$entry->nodeValue = (string)$this->value;
		}
		
		$document->save($this->file->getAbsolutePath());
		
		$this->log(sprintf('Writing configuration file %s with new data for %s (%s)',
			$this->file->getAbsolutePath(), $this->path, $this->value), Project::MSG_INFO);
	}
}

?>
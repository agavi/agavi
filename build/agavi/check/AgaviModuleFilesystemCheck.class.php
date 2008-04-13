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

/**
 * Determines whether a given directory is a valid Agavi module.
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
class AgaviModuleFilesystemCheck extends AgaviFilesystemCheck
{
	/**
	 * @var        string The relative path to the project actions
	 *                    directory.
	 */
	protected $actionsDirectory = 'actions';
	
	/**
	 * @var        string The relative path to the project views
	 *                    directory.
	 */
	protected $viewsDirectory = 'views';
	
	/**
	 * @var        string The relative path to the project templates
	 *                    directory.
	 *
	 */
	protected $templatesDirectory = 'templates';
	
	/**
	 * @var        string The relative path to the project configuration
	 *                    directory.
	 */
	protected $configDirectory = 'config';

	/**
	 * Sets the actions directory.
	 *
	 * @param      string The actions directory.
	 */
	public function setActionsDirectory($actionsDirectory)
	{
		$this->actionsDirectory = $actionsDirectory;
	}
	
	/**
	 * Gets the actions directory.
	 *
	 * @return     string The actions directory.
	 */
	public function getActionsDirectory()
	{
		return $this->actionsDirectory;
	}
	
	/**
	 * Sets the views directory.
	 *
	 * @param      string The views directory.
	 */
	public function setViewsDirectory($viewDirectory)
	{
		$this->viewsDirectory = $viewsDirectory;
	}
	
	/**
	 * Gets the views directory.
	 *
	 * @return     string The views directory.
	 */
	public function getViewsDirectory()
	{
		return $this->viewsDirectory;
	}
	
	/**
	 * Sets the templates directory.
	 *
	 * @param      string The templates directory.
	 */
	public function setTemplatesDirectory($templatesDirectory)
	{
		$this->templatesDirectory = $templatesDirectory;
	}
	
	/**
	 * Gets the templates directory.
	 *
	 * @return     string The templates directory.
	 */
	public function getTemplatesDirectory()
	{
		return $this->templatesDirectory;
	}
	
	/**
	 * Sets the configuration directory.
	 *
	 * @param      string The configuration directory.
	 */
	public function setConfigDirectory($configDirectory)
	{
		$this->configDirectory = $configDirectory;
	}
	
	/**
	 * Gets the configuration directory.
	 *
	 * @return     string The configuration directory.
	 */
	public function getConfigDirectory()
	{
		return $this->configDirectory;
	}
	
	/**
	 * Determines whether a given directory is a valid Agavi module.
	 *
	 * @return     bool True if the directory is valid; false otherwise.
	 */
	public function check()
	{
		$path = $this->getPath();
		if(is_dir($path))
		{
			if(is_dir($path . '/' . $this->actionsDirectory) &&
				is_dir($path . '/' . $this->viewsDirectory) &&
				is_dir($path . '/' . $this->templatesDirectory) &&
				is_dir($path . '/' . $this->configDirectory))
			{
				if(file_exists($path . '/' . $this->configDirectory . '/module.xml'))
				{
					return true;
				}
			}
		}
		return false;
	}
}

?>
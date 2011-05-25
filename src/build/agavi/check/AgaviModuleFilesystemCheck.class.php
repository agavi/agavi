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

/**
 * Determines whether a given directory is a valid Agavi module.
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
class AgaviModuleFilesystemCheck extends AgaviFilesystemCheck
{
	/**
	 * @var        string The relative path to the project configuration directory
	 */
	protected $configDirectory = 'config';

	/**
	 * Sets the configuration directory.
	 *
	 * @param      string The configuration directory.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setConfigDirectory($configDirectory)
	{
		$this->configDirectory = $configDirectory;
	}
	
	/**
	 * Gets the configuration directory.
	 *
	 * @return     string The configuration directory.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getConfigDirectory()
	{
		return $this->configDirectory;
	}
	
	/**
	 * Determines whether a given directory is a valid Agavi module.
	 *
	 * @return     bool True if the directory is valid; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function check()
	{
		$path = $this->getPath();
		if(is_dir($path) &&
			is_dir($path . '/' . $this->configDirectory) &&
			file_exists($path . '/' . $this->configDirectory . '/module.xml')) {
			return true;
		}
		return false;
	}
}

?>
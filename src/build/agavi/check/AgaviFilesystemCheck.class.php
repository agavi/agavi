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
 * Determines whether a given path is valid.
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
abstract class AgaviFilesystemCheck extends AgaviCheck
{
	/**
	 * @var        string The path to check.
	 */
	protected $path = '';
	
	/**
	 * Sets the path to check.
	 *
	 * @param      string The path to check.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}
	
	/**
	 * Gets the path to check.
	 *
	 * @return     string The path to check.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPath()
	{
		return $this->path;
	}
}

?>
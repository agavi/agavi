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
 * Transforms an input array to a delimited string.
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
class AgaviArraytostringTransform extends AgaviTransform
{
	/**
	 * @var        string The separator between array elements.
	 */
	protected $delimiter = ' ';
	
	/**
	 * Sets the delimiter.
	 *
	 * @param      string The delimiter for the output string.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}
	
	/**
	 * Transforms an input array to a delimited string.
	 *
	 * @return     string The output string.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function transform()
	{
		$input = $this->getInput();
		
		if($input === null || !is_array($input)) {
			return $input;
		}
		
		$input = str_replace('"', '\\"', $input);
		$input = '"' . implode('"' . $this->delimiter . '"', $input) . '"';
		
		return $input;
	}
}

?>
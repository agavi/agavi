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
 * Represents any transformation that may occur for a given input.
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
abstract class AgaviTransform
{
	protected $input = null;

	/**
	 * Sets the input.
	 *
	 * @param      mixed The input to transform.
	 */
	public function setInput($input)
	{
		$this->input = $input;
	}

	/**
	 * Gets the input.
	 *
	 * @return     mixed The input to be transformed.
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Transforms the input according to the parameters of the transformation.
	 *
	 * @return     mixed The result of the transformation.
	 */
	abstract public function transform();
}

?>
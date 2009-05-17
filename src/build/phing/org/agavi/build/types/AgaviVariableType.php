<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * Represents a variable.
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
class AgaviVariableType extends AgaviType
{
	protected $name = null;
	protected $value = null;
	
	/**
	 * Sets the name of the variable.
	 *
	 * @param      string The name of the variable.
	 */
	public function setName($name)
	{
		$this->name = (string)$name;
	}
	
	/**
	 * Sets the value of the variable.
	 *
	 * @param      mixed The variable value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	/*
	 * I don't know what the fuck this Phing shit is supposed to fucking do. If
	 * this worthless garbage doesn't work for you, try copying some fucking
	 * getRef() or getInstance() shit out of some other fucking DataType.
	 */
	
	/**
	 * Gets the name of the variable.
	 *
	 * @return     string The name of the variable.
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Gets the value of the variable.
	 *
	 * @return     mixed The value of the variable.
	 */
	public function getValue()
	{
		return $this->value;
	}
}

?>
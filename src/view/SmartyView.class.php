<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     zembla {@link http://forum.mojavi.org/index.php?showuser=329}
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviSmartyView extends AgaviView
{
	public function clearAttributes()
	{
		$this->smarty->clear_all_assign();
	}

	public function getAttributeNames()
	{
		return array_keys($this->smarty->get_template_vars());
	}

	public function hasAttribute($name)
	{
		return !is_null($this->smarty->get_template_vars($name));
	}

	public function & getAttribute($name)
	{
		return $this->smarty->get_template_vars($name);
	}

	public function & removeAttribute($name)
	{
		$retval = $this->smarty->get_template_vars($name);
		$this->smarty->clear_assign($name);
		return $retval;
	}

	public function setAttribute($name, $value)
	{
		$this->smarty->assign($name, $value);
	}

	public function appendAttribute($name, $value)
	{
		$this->smarty->append($name, $value);
	}

	public function setAttributeByRef($name, &$value)
	{
		$this->smarty->assign_by_ref($name, $value);
	}

	public function appendAttributeByRef($name, &$value)
	{
		$this->smarty->append_by_ref($name, $value);
	}

	public function setAttributes($attributes)
	{
		$this->smarty->assign($attributes);
	}

	public function setAttributesByRef(&$attributes)
	{
		foreach ($attributes as $key => &$value)
		{
			$this->setAttributeByRef($key, $value);
		}
	}

	public function & getEngine()
	{
		return $this->smarty;
	}

}

?>
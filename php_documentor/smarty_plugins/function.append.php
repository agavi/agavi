<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * Append data to an existing smarty variable.
 *
 * @param array  An array of parameters.
 * @param Smarty A Smarty class instance.
 *
 * @return void
 */
function smarty_function_append ($params, &$smarty)
{

	extract($params);

	if (empty($var))
	{

		$smarty->trigger_error("append: missing 'var' parameter");
		return;

	}

	if (empty($value))
	{

		$smarty->trigger_error("append: missing 'value' parameter");
		return;

	}

	$varValue  = $smarty->get_template_vars($var);
	$varValue .= $value;

	$smarty->assign($var, $varValue);

}

?>

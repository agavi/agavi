<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+
// | This script builds our menu.                                              |
// +---------------------------------------------------------------------------+

// menu links
$links = array();
$links['default_action']         = array(MO_DEFAULT_MODULE,         MO_DEFAULT_ACTION);
$links['error_404_action']       = array(MO_ERROR_404_MODULE,       MO_ERROR_404_ACTION);
$links['login_action']           = array(MO_LOGIN_MODULE,           MO_LOGIN_ACTION);
$links['module_disabled_action'] = array(MO_MODULE_DISABLED_MODULE, MO_MODULE_DISABLED_ACTION);
$links['secure_action']          = array(MO_SECURE_MODULE,          MO_SECURE_ACTION);
$links['unavailable_action']     = array(MO_UNAVAILABLE_MODULE,     MO_UNAVAILABLE_ACTION);

// get the controller
$controller = $this->getContext()->getController();

// loop through our links and format them
foreach ($links as $key => &$parameters)
{

	$parameters = array(MO_MODULE_ACCESSOR => $parameters[0],
						MO_ACTION_ACCESSOR => $parameters[1]);

	$links[$key] = $controller->genURL(null, $parameters);

}

// set the links attribute
$this->setAttributeByRef('link', $links);

?>

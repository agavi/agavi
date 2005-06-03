<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+
// | This script builds our menu.                                              |
// +---------------------------------------------------------------------------+

// menu links
$links = array();
$links['default_action']         = array(AG_DEFAULT_MODULE,         AG_DEFAULT_ACTION);
$links['error_404_action']       = array(AG_ERROR_404_MODULE,       AG_ERROR_404_ACTION);
$links['login_action']           = array(AG_LOGIN_MODULE,           AG_LOGIN_ACTION);
$links['module_disabled_action'] = array(AG_MODULE_DISABLED_MODULE, AG_MODULE_DISABLED_ACTION);
$links['secure_action']          = array(AG_SECURE_MODULE,          AG_SECURE_ACTION);
$links['unavailable_action']     = array(AG_UNAVAILABLE_MODULE,     AG_UNAVAILABLE_ACTION);

// get the controller
$controller = $this->getContext()->getController();

// loop through our links and format them
foreach ($links as $key => &$parameters)
{

	$parameters = array(AG_MODULE_ACCESSOR => $parameters[0],
						AG_ACTION_ACCESSOR => $parameters[1]);

	$links[$key] = $controller->genURL(null, $parameters);

}

// set the links attribute
$this->setAttributeByRef('link', $links);

?>

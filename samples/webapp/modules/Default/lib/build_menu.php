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
// | This script builds our menu.                                              |
// +---------------------------------------------------------------------------+

// menu links
$links = array();
$links['default_action']         = array(AgaviConfig::get('actions.default_module'),         AgaviConfig::get('actions.default_action'));
$links['error_404_action']       = array(AgaviConfig::get('actions.error_404_module'),             AgaviConfig::get('actions.error_404_action'));
$links['login_action']           = array(AgaviConfig::get('actions.login_module'),           AgaviConfig::get('actions.login_action'));
$links['module_disabled_action'] = array(AgaviConfig::get('actions.module_disabled_module'), AgaviConfig::get('actions.module_disabled_action'));
$links['secure_action']          = array(AgaviConfig::get('actions.secure_module'),          AgaviConfig::get('actions.secure_action'));
$links['unavailable_action']     = array(AgaviConfig::get('actions.unavailable_module'),     AgaviConfig::get('actions.unavailable_action'));

// get the controller
$controller = $this->getContext()->getController();
$request = $this->getContext()->getRequest();

$ma = $request->getModuleAccessor();
$aa = $request->getActionAccessor();

// loop through our links and format them
foreach ($links as $key => &$parameters)
{

	$parameters = array(
		$ma => $parameters[0],
		$aa => $parameters[1]
	);

	$links[$key] = $controller->genURL(null, $parameters);

}

// set the links attribute
$this->setAttributeByRef('link', $links);

?>
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
 * AgaviAuthorizationFilter performs security checks before Action execution.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviAuthorizationFilter extends AgaviFilter
{
	/**
	 * Execute this filter.
	 *
	 * AgaviAuthorizationFilter performs security checks before Action execution.
	 * This default implementation calls a method named "checkPermissions" on the
	 * Action and passes AgaviUser and the current AgaviRequestDataHolder as args.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$action = $container->getActionInstance();
		
		$checkPermissionsMethod = 'check' . $container->getRequestMethod() . 'Permissions';
		if(!method_exists($action, $checkPermissionsMethod)) {
			$checkPermissionsMethod = 'checkPermissions';
		}
		
		// TODO: do we need to wrap this in a try/catch block? what happens if an exception is thrown in checkPermissions()?
		if($action->$checkPermissionsMethod($this->getContext()->getUser(), $container->getRequestData())) {
			$filterChain->execute($container);
		} else {
			// TODO: allow actions to handle this case e.g. through handleDenial() or something like that?
			// this exception will bubble up to the security filter and cause a forward to the "secure" action there
			throw new AgaviSecurityException();
		}
	}
}

?>
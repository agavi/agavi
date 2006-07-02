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
 * AgaviIValidatorContainer is an interface for classes which contains several
 * child validators
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
interface AgaviIfValidatorContainer
{
	/**
	 * adds a new validator to the list of children
	 * 
	 * @param      AgaviValidator $validator new child
	 */
	public function addChild (AgaviValidator $validator);
	
	/**
	 * fetches the request
	 * 
	 * @return     AgaviRequest the request to be used by child validators
	 */
	public function getRequest ();
	
	/**
	 * fetches the dependency manager
	 * 
	 * @return     AgaviDependencyManager the dependency manager to be used
	 *                                    by child validators
	 */
	public function getDependencyManager ();
	
	/**
	 * fetches the error manager
	 * 
	 * @return     AgaviErrorManager the error manager to be used by child
	 *                               validators
	 */
	public function getErrorManager (); 
}
?>

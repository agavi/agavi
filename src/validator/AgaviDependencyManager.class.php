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
 * AgaviDependencyManager handles the dependencies in the validation process
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDependencyManager
{
	/**
	 * @var array already provided tokens.
	 */
	protected $depData = array();
	
	/**
	 * Clears the dependency cache.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->depData = array();
	}
	
	/**
	 * Checks whether a list of dependencies is met.
	 * 
	 * @param      array  The list of dependencies that have to meet.
	 * @param      AgaviVirtualArrayPath The base path to which all tokens are 
	 *                                   appended.
	 * 
	 * @return     bool all dependencies are met
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function checkDependencies(array $tokens, AgaviVirtualArrayPath $base)
	{
		$root = new AgaviVirtualArrayPath('');
		foreach($tokens as $token) {
			$path = $root;
			if(substr($token, 0, 1) == '[') {
				// the dependency we need to check is relative
				$path = $base;
			}

			if(!$path->getValueByChildPath($token, $this->depData)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Puts a list of tokens into the dependency cache.
	 * 
	 * @param      array  The list of new tokens.
	 * @param      AgaviVirtualArrayPath The base path to which all tokens are 
	 *                                   appended.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addDependTokens(array $tokens, AgaviVirtualArrayPath $base)
	{
		foreach($tokens as $token) {
			$base->setValueByChildPath($token, $this->depData, true);
		}
	}
}
?>
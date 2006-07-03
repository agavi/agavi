<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDependencyManager
{
	/**
	 * @var array already provided tokens
	 */
	private $DepData = array();
	
	/**
	 * clears the dependency cache
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->DepData = array();
	}
	
	/**
	 * checks whether a list dependencies are met
	 * 
	 * @param      array  list of dependencies that have to meet
	 * @param      string base path to which all tokens are appended
	 * 
	 * @return     bool all dependencies are met
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function checkDependencies($tokens, $base = '')
	{
		foreach($tokens AS $token) {
			if(!AgaviPath::getValueByPath($this->DepData, $base.'/'.$token)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * puts a list of tokens into the dependency cache
	 * 
	 * @param      array  list of new tokens
	 * @param      string base path to which all tokens are appended
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addDependTokens($tokens, $base = '') {
		foreach($tokens AS $token) {
			AgaviPath::setValueByPath($this->DepData, $base.'/'.$token, true);
		}
	}
}
?>

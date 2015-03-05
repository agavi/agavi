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
		$currentParts = $base->getParts();
		foreach($tokens as $token) {
			if($currentParts && strpos($token, '%') !== false) { 
				// the depends attribute contains sprintf syntax 
				$token = vsprintf($token, $currentParts); 
			}
			
			$path = new AgaviVirtualArrayPath($token);
			if(!$path->getValue($this->depData)) {
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
		$currentParts = $base->getParts();
		foreach($tokens as $token) {
			if($currentParts && strpos($token, '%') !== false) { 
				// the depends attribute contains sprintf syntax 
				$token = vsprintf($token, $currentParts); 
			}
			
			$path = new AgaviVirtualArrayPath($token);
			$path->setValue($this->depData, true);
		}
	}
	
	/**
	 * Populate key references in an argument base string if necessary.
	 * Fills only empty bracket positions with an sprintf() offset placeholder.
	 * Example: foo[][bar][] as input will return foo[%2$s][bar][%4$s] as output.
	 * This is used in validate.xsl to convert pre-1.1 provides/depends behavior.
	 *
	 * @param      string The argument base string.
	 *
	 * @return     string The argument base string with empty brackets filled with
	 *                    correct sprintf() position specifiers.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public static function populateArgumentBaseKeyRefs($string)
	{
		$index = 1;
		return preg_replace_callback(
			'#\[([^\]]*)\]#',
			function($matches) use(&$index) {
				$index++; // always increment so static key parts are "skipped" properly
				return $matches[1] !== '' ? $matches[0] : '[%'.$index.'$s]'; // leave parts other than "[]" intact, else inject numeric accessor
			},
			$string
		);
	}
	
	/*
	 * Returns the list of provided tokens from the dependency cache.
	 *
	 * @return     array Provided tokens from the dependency cache.
	 *
	 * @author     Steffen Gransow <agavi@mivesto.de>
	 * @since      1.0.8
	 */
	public function getDependTokens()
	{
		return $this->depData;
	}
}

?>

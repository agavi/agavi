<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * AgaviRecursiveDirectoryFilterIterator filters a RecursiveDirectoryIterator
 * with a given set of include and exclude patterns.
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviRecursiveDirectoryFilterIterator extends RecursiveFilterIterator
{
	/**
	 * The list of default excludes
	 * @var          array
	 */
	public static $defaultExcludes = array('.', '..', '.svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr');
	
	/**
	 * @var          array the list of excludes
	 */
	protected $excludes = array();
	
	/**
	 * @var          array the list of include patterns
	 */
	protected $includes = array();
	
	/**
	 * Creates a new AgaviRecursiveDirectoryFilterIterator.
	 * 
	 * @var          RecursiveDirectoryIterator the directory iterator to decorate
	 * @var          array the list of include patterns (regular expressions)
	 * @var          array the list of exclude patterns (literal)
	 * @var          boolean whether to use the default exclude patterns.
	 */
	public function __construct(RecursiveDirectoryIterator $iterator, array $includes = array(), array $excludes = array(), $noDefaultExcludes = false)
	{
		parent::__construct($iterator);
		if(!$noDefaultExcludes) {
			$this->excludes = array_merge($excludes, self::$defaultExcludes);
		} else {
			$this->excludes = $excludes;
		}
		
		foreach($includes as $pattern) {
			$this->includes[] = '!'.str_replace('!', '\!', $pattern).'!i';
		}
	}
	
	/**
	 * Checks whether the current item is included.
	 * 
	 * An item is included if it is matched by any of the include expressions
	 * and none of the exclude patterns.
	 * 
	 * @return       boolean true if the item is included
	 */
	public function accept()
	{
		if(!$this->isIncluded()) {
			return false;
		}
		if($this->isExcluded()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Checks whether the current item is matched by an include expression.
	 * 
	 * Directories are always included.
	 * 
	 * @return       boolean true if the items path matches an include expression
	 */
	protected function isIncluded() {
		if(empty($this->includes)) {
			return true;
		}
		if($this->current()->isDir()) {
			return true;
		}
		foreach($this->includes as $pattern) {
			if(preg_match($pattern, $this->current()->getPathName())) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Checks whether the item is matched by any of the exclude expressions.
	 * 
	 * @return       boolean true if the items name equals an exclude pattern.
	 */
	protected function isExcluded() {
		return in_array($this->current()->getFilename(), $this->excludes);
	}
	
	/**
	 * Returns a child iterator.
	 * 
	 * @return       AgaviRecursiveDirectoryFilterIterator an iterator for a subdirectory
	 */
	public function getChildren()
	{
		$it = parent::getChildren();
		if(null !== $it) {
			$it->excludes = $this->excludes;
			$it->includes = $this->includes;
		}
		return $it;
	}
}

?>
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
 * AgaviFilterChain manages registered filters for a specific context.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviFilterChain
{
	/**
	 * @var        array An array to keep track of filter execution.
	 */
	protected static $filterLog;
	
	/**
	 * @var        string The unique key to access the list of filters and their
	 *                    execution count for this filter chain's Context.
	 */
	protected $filterLogKey = '';
	
	/**
	 * @var        array The elements in this chain.
	 */
	protected $chain = array();
	
	/**
	 * @var        int The current position in the chain.
	 */
	protected $index = -1;
	
	/**
	 * @var        AgaviRespinse The Response instance that is handed to filters.
	 */
	protected $response = null;

	/**
	 * Initialize this Filter Chain.
	 *
	 * @param      AgaviResponse the Response instance for this Chain.
	 * @param      array An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviResponse $response, $parameters = array())
	{
		$this->response = $response;
		$this->filterLogKey = $response->getContext()->getName();
	}
	
	/**
	 * Execute the next filter in this chain.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function execute()
	{
		// skip to the next filter
		$this->index++;

		if($this->index < count($this->chain)) {
			// execute the next filter
			$filter = $this->chain[$this->index];
			$count = ++self::$filterLog[$this->filterLogKey][get_class($filter)];
			if($count == 1) {
				$filter->executeOnce($this, $this->response);
			} else {
				$filter->execute($this, $this->response);
			}
		}
	}

	/**
	 * Register a filter with this chain.
	 *
	 * @param      AgaviFilter A Filter implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function register($filter)
	{
		$this->chain[] = $filter;
		self::$filterLog[$this->filterLogKey][get_class($filter)] = 0;
	}
}

?>
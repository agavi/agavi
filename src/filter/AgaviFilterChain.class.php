<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviFilterChain
{
	/**
	 * @constant   string Filter chain type identifier "action".
	 */
	const TYPE_ACTION = 'action';
	
	/**
	 * @constant   string Filter chain type identifier "global".
	 */
	const TYPE_GLOBAL = 'global';
	
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
	 * @var        AgaviExecutionContainer The execution container that is handed to filters.
	 */
	protected $context = null;

	/**
	 * @var        string The type of filter chain.
	 * @see        AgaviFilterChain::TYPE_ACTION
	 * @see        AgaviFilterChain::TYPE_GLOBAL
	 */
	protected $type = self::TYPE_ACTION;
	
	/**
	 * Initialize this Filter Chain.
	 *
	 * @param      AgaviResponse the Response instance for this Chain.
	 * @param      array An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$this->filterLogKey = $context->getName();
	}
	
	/**
	 * Set the type of this filter chain.
	 *
	 * @see        AgaviFilterChain::TYPE_ACTION
	 * @see        AgaviFilterChain::TYPE_GLOBAL
	 *
	 * @param      string The type identifier.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Get the type of this filter chain.
	 *
	 * @see        AgaviFilterChain::TYPE_ACTION
	 * @see        AgaviFilterChain::TYPE_GLOBAL
	 *
	 * @return     string The type identifier.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Execute the next filter in this chain.
	 *
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function execute(AgaviExecutionContainer $container)
	{
		if($filter = current($this->chain)) {
			// advance the pointer immediately; the next filter will call this again
			next($this->chain);
			$count = ++self::$filterLog[$this->filterLogKey][$fc = get_class($filter)];
			if($count == 1 && method_exists($filter, 'executeOnce')) {
				trigger_error(sprintf('Filter "%s" is implementing the deprecated method AgaviIFilter::executeOnce(); support will be removed in Agavi 1.2. Please refer to UPGRADING or ticket #1410 for details.', $fc), E_USER_DEPRECATED);
				$filter->executeOnce($this, $container);
			} else {
				$filter->execute($this, $container);
			}
		}
	}

	/**
	 * Get a named filter instance from this chain.
	 *
	 * @param      string The name of the filter in this chain.
	 *
	 * @return     AgaviIFilter The filter instance, or null if no such filter.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getFilter($name)
	{
		if(isset($this->chain[$name])) {
			return $this->chain[$name];
		}
	}

	/**
	 * Register a filter with this chain.
	 *
	 * @param      AgaviIFilter A Filter implementation instance.
	 * @param      string       The filter name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function register(AgaviIFilter $filter, $name)
	{
		$this->chain[$name] = $filter;
		$filterClass = get_class($filter);
		if(!isset(self::$filterLog[$this->filterLogKey][$filterClass])) {
			self::$filterLog[$this->filterLogKey][$filterClass] = 0;
		}
	}
}

?>
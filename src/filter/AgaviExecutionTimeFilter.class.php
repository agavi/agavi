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
 * AgaviExecutionTimeFilter tracks the length of time it takes for an entire
 * request to be served starting with the dispatch and ending when the last 
 * action request has been served.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>comment</b> - [Yes] - Should we add an HTML comment to the end of each
 *                            output with the execution time?
 * # <b>replace</b> - [No] - If this exists, every occurrence of the value in the
 *                           client response will be replaced by the execution
 *                           time.
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
class AgaviExecutionTimeFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$context = $this->getContext();
		
		$comment = $this->getParameter('comment', false);
		$replace = $this->getParameter('replace', false);
		
		$start = microtime(true);
		$filterChain->execute($container);
		
		$response = $container->getResponse();
		
		$outputTypes = (array) $this->getParameter('output_types');
		if(!$response->isContentMutable() || (is_array($outputTypes) && !in_array($response->getOutputType()->getName(), $outputTypes))) {
			return;
		}
		
		$time = (microtime(true) - $start);
		
		
		if($replace) {
			$output = $response->getContent();
			$output = str_replace($replace, $time, $output);
			$response->setContent($output);
		}
		
		if($comment) {
			if($comment === true) {
				$comment = "\n\n<!-- This page took %s seconds to process -->";
			}
			$response->appendContent(sprintf($comment, $time));
		}
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during 
	 *                                         initialization.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// set defaults
		$this->setParameter('comment', true);
		$this->setParameter('replace', null);
		$this->setParameter('output_types', null);

		// initialize parent
		parent::initialize($context, $parameters);
	}
}

?>
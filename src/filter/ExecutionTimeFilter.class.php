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
 * AgaviExecutionTimeFilter tracks the length of time it takes for an entire
 * request to be served starting with the dispatch and ending when the last 
 * action request has been served.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>comment</b> - [Yes] - Should we add an HTML comment to the end of each
 *                            output with the execution time?
 * # <b>replace</b> - [No] - If this exists, every occurance of the value in the
 *                           client response will be replaced by the execution
 *                           time.
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
class AgaviExecutionTimeFilter extends AgaviFilter
{

	/**
	 * Calculate the execution time.
	 *
	 * @param      string The start microtime.
	 * @param      string The end microtime.
	 *
	 * @return     double The execution time in seconds.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	private function calculateTime ($start, $end)
	{

		$end   = explode(' ', $end);
		$start = explode(' ', $start);

		$end   = (float) $end[1] + (float) $end[0];
		$start = (float) $start[1] + (float) $start[0];

		return number_format($end - $start, 4);

	}

	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain The filter chain.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute ($filterChain)
	{

		static $loaded;

		if (!isset($loaded))
		{

			// load the filter
			$loaded = true;

			// grab parameters
			$comment = $this->getParameter('comment');
			$replace = $this->getParameter('replace', false);

			if ($replace)
			{

				// we have to buffer the output in order to replace
				// the keywords

				// track start time
				$start = microtime();

				// turn on buffering
				ob_start();

				// execute next filter
				$filterChain->execute();

				// grab buffer
				$buffer = ob_get_contents();

				// stop buffering
				ob_end_clean();

				// track end time
				$end = microtime();

				// calculate time
				$time = $this->calculateTime($start, $end);

				// replace keyword in buffer
				$buffer = str_replace($replace, $time, $buffer);

				// print the modified buffer to the client
				echo $buffer;

			} else
			{

				// we're not replacing any keywords so process normally

				// track start time
				$start = microtime();

				// execute next filter
				$filterChain->execute();

				// track end time
				$end = microtime();

				// calculate time
				$time = $this->calculateTime($start, $end);

			}

			// should we print an HTML comment?
			if ($comment === true)
			{

				echo "\n\n";
				echo '<!-- This page took ' . $time .
				     ' seconds to process. -->';

			}

		} else
		{

			// we already loaded this filter, skip to the next filter
			$filterChain->execute();

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
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		// set defaults
		$this->setParameter('comment', true);
		$this->setParameter('replace', null);

		// initialize parent
		parent::initialize($context, $parameters);
	}

}

?>
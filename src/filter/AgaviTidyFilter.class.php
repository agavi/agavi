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
 * AgaviTidyFilter cleans up (X)HTML or XML using the tidy extension.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviTidyFilter extends AgaviFilter implements AgaviIGlobalFilter
{
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		// nothing to do so far. let's carry on in the chain
		$filterChain->execute($container);
		
		// fetch some prerequisites
		$response = $container->getResponse();
		$ot = $response->getOutputType();
		$cfg = $this->getParameters();
		
		if(!$response->isContentMutable() || !($output = $response->getContent())) {
			// content empty or response not mutable; it's over!
			return;
		}
		
		if(is_array($cfg['methods']) && !in_array($container->getRequestMethod(), $cfg['methods'])) {
			// we're not allowed to run for this request method
			return;
		}
		
		if(is_array($cfg['output_types']) && !in_array($ot->getName(), $cfg['output_types'])) {
			// we're not allowed to run for this output type
			return;
		}
		
		$tidy = new tidy();
		$tidy->parseString($output, $cfg['tidy_options'], $cfg['tidy_encoding']);
		$tidy->cleanRepair();
		
		if($tidy->getStatus()) {
			// warning or error occurred
			$emsg = sprintf(
				'Tidy Filter encountered the following problems while parsing and cleaning the document: ' . "\n\n%s",
				$tidy->errorBuffer
			);
			
			if(AgaviConfig::get('core.use_logging') && $cfg['log_errors']) {
				$lmsg = $emsg . "\n\nResponse content:\n\n" . $response->getContent();
				$lm = $this->context->getLoggerManager();
				$mc = $lm->getDefaultMessageClass();
				$m = new $mc($lmsg, $cfg['logging_severity']);
				$lm->log($m, $cfg['logging_logger']);
			}
			
			// all in all, that didn't go so well. let's see if we should just silently abort instead of throwing an exception
			if(!$cfg['ignore_errors']) {
				throw new AgaviParseException($emsg);
			}
		}
		
		$response->setContent((string)$tidy);
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during
	 *                                         initialization
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// set defaults
		$this->setParameters(array(
			'methods'          => null,
			'output_types'     => null,
			
			'tidy_options'     => array(),
			'tidy_encoding'    => null,
			
			'ignore_errors'    => true,
			'log_errors'       => true,
			'logging_severity' => AgaviLogger::WARN,
			'logging_logger'   => null,
		));
		
		// initialize parent
		parent::initialize($context, $parameters);
	}
}

?>
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
 * AgaviHttpRedirectRoutingCallback allows redirection of a matched route to a
 * route or URL. Matched arguments can be rewritten.
 *
 * You need to configure this callback using parameters in the <callback> block.
 *
 * To redirect to a URL, use the "url" configuration parameter and supply the
 * destination URL as the value.
 *
 * To redirect to a route, use the "route" configuration parameter and supply
 * the name of the route to generate.
 * You may pass an arbitrary array of arguments in parameter "arguments". If a
 * parameter value contains a valid PHP variable literal such as $foo, ${foo} or
 * {$foo}, the literal will be replaced with the value of the argument "foo" in
 * the matched route the callback is defined on.
 * Default routing gen() options for generating are "relative" set to false and
 * "separator" set to "&". You may pass an array of options or the name of a
 * routing gen() options preset in configuration in parameter "options".
 *
 * By default, the HTTP status code 302 is used for redirects. You can define a
 * different status code through configuration parameter "code".
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviHttpRedirectRoutingCallback extends AgaviRoutingCallback
{
	/**
	 * Initialize the callback instance.
	 *
	 * @param      AgaviResponse An AgaviResponse instance.
	 * @param      array         An array with information about the route.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function initialize(AgaviContext $context, array &$route)
	{
		parent::initialize($context, $route);
		
		// sanity check
		if(!($context->getRouting() instanceof AgaviWebRouting)) {
			throw new AgaviException('AgaviHttpRedirectRoutingCallback can only be used in combination with AgaviWebRouting.');
		}
	}
	
	/**
	 * Gets executed when the route of this callback route matched.
	 *
	 * @param      array                   The parameters generated by this route.
	 * @param      AgaviExecutionContainer The original execution container.
	 *
	 * @return     bool Whether the routing should handle the route as matched.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		$routing = $this->getContext()->getRouting();
		
		if($this->hasParameter('route')) {
			// generate a route
			$route = $this->getParameter('route');
			
			$arguments = (array)$this->getParameter('arguments');
			// expand ${foo} in arguments using incoming parameters, this enables basic rewriting of arguments
			array_walk_recursive($arguments, function(&$argument) use($parameters) { $argument = AgaviToolkit::expandVariables($argument, $parameters); });
			
			$options = $this->getParameter('options', array());
			// prepare options; make sure URLs are absolute and separator is "&" by default
			if(is_array($options)) {
				// it's an array of options, not a gen options preset name; set our defaults
				if(!isset($options['separator'])) {
					$options['separator'] = '&';
				}
				if(!isset($options['relative'])) {
					$options['relative'] = false;
				}
			}
			
			$url = $routing->gen($route, $arguments, $options);
		} elseif($this->hasParameter('url')) {
			// just a plain URL to redirect to, but we still expand arguments
			$url = AgaviToolkit::expandVariables(
				$this->getParameter('url'),
				array_map(
					function($value) use($routing) {
						if(is_scalar($value)) {
							return $routing->escapeOutputParameter($value);
						} else {
							return '';
						}
					},
					$parameters
				)
			);
		} else {
			// improper configuration for whatever reason; bail out
			return false;
		}
		
		// create response and set redirect
		$response = $this->getContext()->createInstanceFor('response');
		if(!($response instanceof AgaviWebResponse)) {
			throw new AgaviException('AgaviHttpRedirectRoutingCallback can only be used in combination with AgaviWebResponse.');
		}
		$response->setRedirect($url, $this->getParameter('code', 302));
		return $response;
	}
}

?>
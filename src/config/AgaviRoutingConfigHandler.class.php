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
 * AgaviRoutingConfigHandler allows you to specify a list of routes that will
 * be matched against any given string.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRoutingConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/routing/1.0';
	
	/**
	 * @var        array Stores the generated names of unnamed routes.
	 */
	protected $unnamedRoutes = array();
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration 
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'routing');
		
		$routing = AgaviContext::getInstance($this->context)->getRouting();

		// reset the stored route names
		$this->unnamedRoutes = array();

		// clear the routing
		$routing->importRoutes(array());
		$data = array();
		
		foreach($document->getConfigurationElements() as $cfg) {
			if($cfg->has('routes')) {
				$this->parseRoutes($routing, $cfg->get('routes'));
			}
		}

		// we cannot do this:
		// $code = '$this->importRoutes(unserialize(' . var_export(serialize($routing->exportRoutes()), true) . '));';
		// return $this->generate($code, $document->documentURI);
		// because var_export() incorrectly escapes null-byte sequences as \000, which results in a corrupted string, and unserialize() doesn't like corrupted strings
		// this was fixed in PHP 5.2.6, but we're compatible with 5.2.0+
		// see http://bugs.php.net/bug.php?id=37262 and http://bugs.php.net/bug.php?id=42272
		
		return serialize($routing->exportRoutes());
	}

	/**
	 * Takes a nested array of AgaviConfigValueHolder containing the routing
	 * information and creates the routes in the given routing.
	 *
	 * @param      AgaviRouting The routing instance to create the routes in.
	 * @param      mixed        The "roles" node (element or node list)
	 * @param      string       The name of the parent route (if any).
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseRoutes(AgaviRouting $routing, $routes, $parent = null)
	{
		foreach($routes as $route) {
			$pattern = AgaviToolkit::expandDirectives($route->getAttribute('pattern'));
			$opts = array();
			if($route->hasAttribute('imply'))					$opts['imply']				= AgaviToolkit::literalize($route->getAttribute('imply'));
			if($route->hasAttribute('cut'))						$opts['cut']					= AgaviToolkit::literalize($route->getAttribute('cut'));
			if($route->hasAttribute('stop'))					$opts['stop']					= AgaviToolkit::literalize($route->getAttribute('stop'));
			if($route->hasAttribute('name'))					$opts['name']					= AgaviToolkit::expandDirectives($route->getAttribute('name'));
			if($route->hasAttribute('source'))				$opts['source']				= AgaviToolkit::expandDirectives($route->getAttribute('source'));
			if($route->hasAttribute('constraint'))		$opts['constraint']		= array_map('trim', explode(' ', trim(AgaviToolkit::expandDirectives($route->getAttribute('constraint')))));
			// values which will be set when the route matched
			if($route->hasAttribute('action'))				$opts['action']				= AgaviToolkit::expandDirectives($route->getAttribute('action'));
			if($route->hasAttribute('locale'))				$opts['locale']				= AgaviToolkit::expandDirectives($route->getAttribute('locale'));
			if($route->hasAttribute('method'))				$opts['method']				= AgaviToolkit::expandDirectives($route->getAttribute('method'));
			if($route->hasAttribute('module'))				$opts['module']				= AgaviToolkit::expandDirectives($route->getAttribute('module'));
			if($route->hasAttribute('output_type'))		$opts['output_type']	= AgaviToolkit::expandDirectives($route->getAttribute('output_type'));

			if($route->has('ignores')) {
				foreach($route->get('ignores') as $ignore) {
					$opts['ignores'][] = $ignore->getValue();
				}
			}

			if($route->has('defaults')) {
				foreach($route->get('defaults') as $default) {
					$opts['defaults'][$default->getAttribute('for')] = $default->getValue();
				}
			}

			if($route->has('callbacks')) {
				$opts['callbacks'] = array();
				foreach($route->get('callbacks') as $callback) {
					$opts['callbacks'][] = array(
						'class' => $callback->getAttribute('class'),
						'parameters' => $callback->getAgaviParameters(),
					);
				}
			}

			$opts['parameters'] = $route->getAgaviParameters();

			if(isset($opts['name']) && $parent) {
				// don't overwrite $parent since it's used later
				$parentName = $parent;
				if($opts['name'][0] == '.') {
					while($parentName && isset($this->unnamedRoutes[$parentName])) {
						$parentRoute = $routing->getRoute($parentName);
						$parentName = $parentRoute['opt']['parent'];
					}
					$opts['name'] = $parentName . $opts['name'];
				}
			}

			if(isset($opts['action']) && $parent) {
				if($opts['action'][0] == '.') {
					$parentRoute = $routing->getRoute($parent);
					// unwind all empty 'action' attributes of the parent(s)
					while($parentRoute && empty($parentRoute['opt']['action'])) {
						$parentRoute = $routing->getRoute($parentRoute['opt']['parent']);
					}
					if(!empty($parentRoute['opt']['action'])) {
						$opts['action'] = $parentRoute['opt']['action'] . $opts['action'];
					}
				}
			}

			$name = $routing->addRoute($pattern, $opts, $parent);
			if(!isset($opts['name']) || $opts['name'] !== $name) {
				$this->unnamedRoutes[$name] = true;
			}
			if($route->has('routes')) {
				$this->parseRoutes($routing, $route->get('routes'), $name);
			}
		}
	}
}

?>
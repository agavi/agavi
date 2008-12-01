<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRoutingConfigHandler extends AgaviConfigHandler
{
	/**
	 * @var        array Stores the generated names of unnamed routes.
	 */
	protected $unnamedRoutes = array();
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string Name of the executing context (if any).
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
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		$routing = AgaviContext::getInstance($context)->getRouting();

		if($context == null) {
			$context = '';
		}

		// reset the stored route names
		$this->unnamedRoutes = array();

		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, true, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		// clear the routing
		$routing->importRoutes(array());
		$data = array();
		
		foreach($configurations as $cfg) {
			if(isset($cfg->routes)) {
				$this->parseRoutes($routing, $cfg->routes);
			}
		}

		$code = '$this->importRoutes(' . var_export($routing->exportRoutes(), true) . ');';

		return $this->generate($code, $config);
	}

	/**
	 * Takes a nested array of AgaviConfigValueHolder containing the routing
	 * information and creates the routes in the given routing.
	 *
	 * @param      AgaviRouting The routing instance to create the routes in.
	 * @param      array A possibly nested array of AgaviConfigValueHolders.
	 * @param      string The name of the parent route (if any).
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseRoutes(AgaviRouting $routing, $routes, $parent = null)
	{
		foreach($routes as $route) {
			$pattern = $route->getAttribute('pattern');
			$opts = array();
			if($route->hasAttribute('imply'))					$opts['imply']				= AgaviToolkit::literalize($route->getAttribute('imply'));
			if($route->hasAttribute('cut'))						$opts['cut']					= AgaviToolkit::literalize($route->getAttribute('cut'));
			if($route->hasAttribute('stop'))					$opts['stop']					= AgaviToolkit::literalize($route->getAttribute('stop'));
			if($route->hasAttribute('name'))					$opts['name']					= $route->getAttribute('name');
			if($route->hasAttribute('callback'))			$opts['callback']			= $route->getAttribute('callback');
			if($route->hasAttribute('source'))				$opts['source']				= $route->getAttribute('source');
			if($route->hasAttribute('constraint'))		$opts['constraint']		= array_map('trim', explode(' ', trim($route->getAttribute('constraint'))));
			// values which will be set when the route matched
			if($route->hasAttribute('action'))				$opts['action']				= AgaviToolkit::literalize($route->getAttribute('action'));
			if($route->hasAttribute('locale'))				$opts['locale']				= AgaviToolkit::literalize($route->getAttribute('locale'));
			if($route->hasAttribute('method'))				$opts['method']				= AgaviToolkit::literalize($route->getAttribute('method'));
			if($route->hasAttribute('module'))				$opts['module']				= AgaviToolkit::literalize($route->getAttribute('module'));
			if($route->hasAttribute('output_type'))		$opts['output_type']	= AgaviToolkit::literalize($route->getAttribute('output_type'));

			if($route->hasChildren('ignores')) {
				foreach($route->ignores as $ignore) {
					$opts['ignores'][] = $ignore->getValue();
				}
			}

			if($route->hasChildren('defaults')) {
				foreach($route->defaults as $default) {
					$opts['defaults'][$default->getAttribute('for')] = $default->getValue();
				}
			}

			if($route->hasChildren('parameters')) {
				$opts['parameters'] = $this->getItemParameters($route);
			}

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
			if($route->hasChildren('routes')) {
				$this->parseRoutes($routing, $route->routes, $name);
			}
		}
	}
}

?>
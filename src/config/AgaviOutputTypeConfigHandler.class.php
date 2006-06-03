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
 * AgaviModuleConfigHandler reads module configuration files to determine the 
 * status of a module.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviOutputTypeConfigHandler extends AgaviConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration file
	 *                                             does not exist or is not readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false)->configurations, AgaviConfig::get('core.environment'), $context);

		$code = '';
		foreach($configurations as $cfg) {
			$otnames = array();
			foreach($cfg->output_types as $outputType) {
				if(!$outputType->hasAttribute('name')) {
					throw new AgaviConfigurationException('No name specified for an Output Type in ' . $config);
				}
				$otname = $outputType->getAttribute('name');
				if(in_array($otname, $otnames)) {
					throw new AgaviConfigurationException('Duplicate Output Type "' . $otname . '" in ' . $config);
				}
				if(!isset($outputType->renderer) && !$outputType->renderer->getAttribute('class')) {
					throw new AgaviConfigurationException('No renderer specified for Output Type "' . $outputType->getAttribute('name') . '" in ' . $config);
				}
				$otnames[] = $otname;
			}
		
			if(!$cfg->output_types->hasAttribute('default')) {
				throw new AgaviConfigurationException('No default Output Type specified in ' . $config);
			}
		
			if(!in_array($cfg->output_types->getAttribute('default'), $otnames)) {
				throw new AgaviConfigurationException('Non-existent Output Type "' . $cfg->output_types->getAttribute('default') . '" specified as default in ' . $config);
			}

			foreach($cfg->output_types as $outputType) {
				$code .= "\$this->outputTypes['" . $outputType->getAttribute('name') . "'] = array(\n";
				$code .= "  'renderer' => '" . $outputType->renderer->getValue() . "',\n";
				if($outputType->hasAttribute('fallback')) {
					$fallback = $outputType->getAttribute('fallback');
					if(!in_array($fallback, $otnames)) {
						throw new AgaviConfigurationException('Output Type "' . $outputType->getAttribute('name') . '" is configured to fall back to non-existent Output Type "' . $fallback . '" in ' . $config);
					}
					$code .= "  'fallback' => '" . ($fallback == 'default' ? $cfg->output_types->getAttribute('default') : $fallback) . "', \n";
				}
				if($outputType->renderer->hasAttribute('extension')) {
					$code .= "  'extension' => '" . $outputType->renderer->getAttribute('extension') . "', \n";
				}
				if($outputType->renderer->hasAttribute('ignore_decorators')) {
					$code .= "  'ignore_decorators' => " . var_export($this->literalize($outputType->renderer->getAttribute('ignore_decorators')), true) . ",\n";
				}
				if($outputType->renderer->hasAttribute('ignore_slots')) {
					$code .= "  'ignore_slots' => " . var_export($this->literalize($outputType->renderer->getAttribute('ignore_slots')), true) . ",\n";
				}
				if(isset($outputType->parameters) && $outputType->parameters->hasChildren()) {
					$code .= "  'parameters' => array(\n";
					foreach($outputType->parameters as $parameter) {
						$code .= "    '" . $parameter->getAttribute('name') . "' => '" . $parameter->getValue() . "',\n";
					}
					$code .= "  )\n";
				}
				$code .= ");\n";
			}
			$code .= "\$this->outputType = '" . $cfg->output_types->getAttribute('default') . "';\n";
		}

		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by OutputTypeConfigHandler\n" .
				  "// date: %s\n%s\n?>";

		$retval = sprintf($retval, date('m/d/Y H:i:s'), $code);

		return $retval;

	}

}

?>
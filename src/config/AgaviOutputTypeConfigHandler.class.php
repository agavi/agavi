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
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile())->configurations, AgaviConfig::get('core.environment'), $context);

		$data = array();
		$defaultOt = null;
		foreach($configurations as $cfg) {
			$otnames = array();
			foreach($cfg->output_types as $outputType) {
				$otname = $outputType->getAttribute('name');
				if(in_array($otname, $otnames)) {
					throw new AgaviConfigurationException('Duplicate Output Type "' . $otname . '" in ' . $config);
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
				$name = $outputType->getAttribute('name');
				$data[$name] = isset($data[$name]) ? $data[$name] : array_merge($cfg->output_types->getAttribute('default') != $name ? array('fallback' => $cfg->output_types->getAttribute('default')) : array(), array('parameters' => array(), 'renderer_parameters' => array()));
				if(isset($outputType->renderer)) {
					$data[$name]['renderer'] = $outputType->renderer->getAttribute('class');
				} else {
					$data[$name]['renderer'] = null;
				}
				if($outputType->hasAttribute('fallback')) {
					$fallback = $outputType->getAttribute('fallback');
					if(!in_array($fallback, $otnames)) {
						throw new AgaviConfigurationException('Output Type "' . $outputType->getAttribute('name') . '" is configured to fall back to non-existent Output Type "' . $fallback . '" in ' . $config);
					}
					$data[$name]['fallback'] = $fallback == 'default' ? $cfg->output_types->getAttribute('default') : $fallback;
				}
				if($outputType->hasAttribute('exception')) {
					$data[$name]['exception'] = $this->literalize($outputType->getAttribute('exception'));
				}
				if(isset($outputType->renderer)) {
					if($outputType->renderer->hasAttribute('extension')) {
						$data[$name]['extension'] = $outputType->renderer->getAttribute('extension');
					}
					if($outputType->renderer->hasAttribute('ignore_decorators')) {
						$data[$name]['ignore_decorators'] = $this->literalize($outputType->renderer->getAttribute('ignore_decorators'));
					}
					if($outputType->renderer->hasAttribute('ignore_slots')) {
						$data[$name]['ignore_slots'] = $this->literalize($outputType->renderer->getAttribute('ignore_slots'));
					}
					$data[$name]['renderer_parameters'] = $this->getItemParameters($outputType->renderer, $data[$name]['renderer_parameters']);
					if(isset($outputType->renderer->assigns)) {
						$assigns = array();
						foreach($outputType->renderer->assigns as $factory => $varname) {
							$assigns[$factory] = $varname->getValue();
						}
						$data[$name]['renderer_parameters'] = array_merge($data[$name]['renderer_parameters'], array('assigns' =>$assigns));
					}
				}
				$data[$name]['parameters'] = $this->getItemParameters($outputType, $data[$name]['parameters']);
			}

			$defaultOt = $cfg->output_types->getAttribute('default');
		}

		$code = '';
		$code .= "\$this->outputTypes = " . var_export($data, true) . ";\n";
		$code .= "\$this->setOutputType('" . $defaultOt . "');\n";


		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by ".__CLASS__."\n" .
				  "// date: %s GMT\n%s\n?>";

		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), $code);

		return $retval;

	}

}

?>
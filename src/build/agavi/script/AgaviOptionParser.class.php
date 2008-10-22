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

require_once(dirname(__FILE__) . '/AgaviOptionException.class.php');

/**
 * Parses input options to the Agavi script.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviOptionParser
{
	protected $source = array();
	
	protected $passedArguments = array();
	protected $passedOptions = array();
	
	protected $nameSeparator = '=';
	
	protected $shortNamePrefix = '-';
	protected $longNamePrefix = '--';
	
	protected $optionTerminator = '--';
	
	protected $defaults = array(
		'short_names' => array(),
		'long_names' => array(),
		'arguments' => 0,
	);
	
	public function __construct(array $source)
	{
		$this->source = $source;
	}
	
	protected function getSourceNameForShortName($option)
	{
		return $this->shortNamePrefix . $option;
	}
	
	protected function getSourceNameForLongName($option)
	{
		return $this->longNamePrefix . $option;
	}
	
	public function parse()
	{
		$options = array();
		foreach($this->options as $option) {
			$options[] = array_merge($this->defaults, $option);
		}
		
		$source = array_values($this->source);
		$size = count($source);
		
		$i;
		for($i = 0; $i < $size; $i++) {
			if($source[$i] === $this->optionTerminator) {
				break;
			}
			$increment = 0;
			$name = null;
			$handler = null;
			$arguments = array();
			foreach($options as $optionName => $option) {
				if(in_array($source[$i], array_map(array($this, 'getSourceNameForShortName'), $option['short_names'])) ||
					in_array($source[$i], array_map(array($this, 'getSourceNameForLongName'), $option['long_names']))) {
					$arguments = array_slice($source, $i + 1, $option['arguments']);
					if(count($arguments) !== $option['arguments']) {
						throw new AgaviOptionException(
							sprintf('Too few arguments to option %s (%d given, %d expected)', $source[$i], count($arguments), $option['arguments'])
						);
					}
					$increment = $option['arguments'];
					$name = $optionName;
					$handler = $option['handler'];
				}
				else {
					foreach($option['long_names'] as $name) {
						if(strpos($source[$i], $this->longNamePrefix . $name . $this->nameSeparator) === 0) {
							if($option['arguments'] === 1) {
								$arguments[] = substr($source[$i], strpos($source[$i], $this->nameSeparator) + 1);
								$name = $optionName;
								$handler = $options['handler'];
							}
							else {
								throw new AgaviOptionException(
									sprintf('Unexpected number of arguments for %s (1 given, %d expected)', $name, $option['arguments'])
								);
							}
						}
					}
				}
			}
			
			if($handler === null) {
				if (strpos($source[$i], $this->shortNamePrefix) === 0 ||
					strpos($source[$i], $this->longNamePrefix) === 0) {
					throw new AgaviOptionException(sprintf('Unexpected option %s', $source[$i]));
				}
				else {
					/* Accept arguments. */
					break;
				}
			}
			
			$this->passedOptions[$name] = array(
				'source' => $source[$i],
				'arguments' => $arguments,
				'handler' => $handler
			);
			
			$i += $increment;
		}
		
		for(; $i < $size; $i++) {
			$this->passedArguments[] = $source[$i];
		}
		
		foreach($this->passedOptions as $name => $option) {
			call_user_func($option['handler'], $this, $name, $option['arguments'], $this->passedArguments);
		}
	}
	
	public function getPassedArguments()
	{
		return $this->passedArguments;
	}
	
	public function hasPassedArgument($name)
	{
		return in_array($name, $this->passedArguments);
	}
	
	public function getPassedOptions()
	{
		return array_keys($this->passedOptions);
	}
	
	public function hasPassedOption($name)
	{
		return isset($this->passedOptions[(string)$name]);
	}
	
	public function getPassedOption($name)
	{
		return $this->hasPassedOption($name) ? $this->passedOptions[(string)$name] : null;
	}
	
	public function addOption($name, array $shortNames, array $longNames, $handler, $arguments = 0)
	{
		$this->options[(string)$name] = array(
			'short_names' => $shortNames,
			'long_names' => $longNames,
			'handler' => $handler,
			'arguments' => $arguments
		);
	}
	
	public function getOptions()
	{
		return $this->options;
	}
}

?>
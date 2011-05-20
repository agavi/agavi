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

require_once(dirname(__FILE__) . '/AgaviOptionException.class.php');

/**
 * Parses input options and arguments to the Agavi script.
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
	/**
	 * @var        array The data from which options are read.
	 */
	protected $source = array();
	
	/**
	 * @var        array All of the arguments that are passed to the program via
	 *                   the source.
	 *
	 * @see        AgaviOptionParser::$source
	 */
	protected $passedArguments = array();
	
	/**
	 * @var        array All of the options that are passed to the program via
	 *                   the source.
	 *
	 * @see        AgaviOptionParser::$source
	 */
	protected $passedOptions = array();
	
	/**
	 * @var        string The separator character for long option names.
	 */
	protected $nameSeparator = '=';
	
	/**
	 * @var        string The characters that must precede each short option name.
	 */
	protected $shortNamePrefix = '-';
	
	/**
	 * @var        string The characters that must precede each long option name.
	 */
	protected $longNamePrefix = '--';
	
	/**
	 * @var        string The characters that separate the options from the
	 *                    program arguments.
	 */
	protected $optionTerminator = '--';
	
	/**
	 * @var        array Default values for each option.
	 */
	protected $defaults = array(
		'short_names' => array(),
		'long_names' => array(),
		'arguments' => 0,
	);
	
	/**
	 * Creates a new option parser.
	 *
	 * @param      array The source data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(array $source)
	{
		$this->source = $source;
	}
	
	/**
	 * Retrieves the value in the source data that must exist for an option to be
	 * parsed in its short form.
	 *
	 * @param      string The short option name.
	 *
	 * @return     string The value that must be matched in the source data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	protected function getSourceNameForShortName($option)
	{
		return $this->shortNamePrefix . $option;
	}
	
	/**
	 * Retrieves the value in the source data that must exist for an option to be
	 * parsed in its long form.
	 *
	 * @param      string The long option name.
	 *
	 * @return     string The value that must be matched in the source data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	protected function getSourceNameForLongName($option)
	{
		return $this->longNamePrefix . $option;
	}
	
	/**
	 * Parses the source data and calls callbacks for each option passed in the
	 * source.
	 *
	 * @return     string The value that must be matched in the source data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
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
				} else {
					foreach($option['long_names'] as $name) {
						if(strpos($source[$i], $this->longNamePrefix . $name . $this->nameSeparator) === 0) {
							if($option['arguments'] === 1) {
								$arguments[] = substr($source[$i], strpos($source[$i], $this->nameSeparator) + 1);
								$name = $optionName;
								$handler = $options['handler'];
							} else {
								throw new AgaviOptionException(
									sprintf('Unexpected number of arguments for %s (1 given, %d expected)', $name, $option['arguments'])
								);
							}
						}
					}
				}
			}
			
			if($handler === null) {
				if(strpos($source[$i], $this->shortNamePrefix) === 0 ||
					strpos($source[$i], $this->longNamePrefix) === 0) {
					throw new AgaviOptionException(sprintf('Unexpected option %s', $source[$i]));
				} else {
					/* Accept arguments. */
					break;
				}
			}

			$this->passedOptions[$name] = isset($this->passedOptions[$name])
				? $this->passedOptions[$name]
				: array();
			$this->passedOptions[$name][] = array(
				'source' => $source[$i],
				'arguments' => $arguments,
				'handler' => $handler
			);
			
			$i += $increment;
		}
		
		for(; $i < $size; $i++) {
			$this->passedArguments[] = $source[$i];
		}
		
		foreach($this->passedOptions as $name => $options) {
			foreach($options as $option) {
				call_user_func($option['handler'], $this, $name, $option['arguments'], $this->passedArguments);
			}
		}
	}
	
	/**
	 * Retrieves the arguments that have been parsed.
	 *
	 * @return     array The arguments passed through the source data.
	 *
	 * @see        AgaviOptionParser::parse()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPassedArguments()
	{
		return $this->passedArguments;
	}
	
	/**
	 * Determines whether a given argument was passed in the source.
	 *
	 * @param      string The argument name.
	 *
	 * @return     bool True if the argument exists in the array of passed
	 *                  arguments; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasPassedArgument($name)
	{
		return in_array($name, $this->passedArguments);
	}
	
	/**
	 * Retrieves the names of the options that have been parsed.
	 *
	 * @return     array The option names passed through the source data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPassedOptions()
	{
		return array_keys($this->passedOptions);
	}
	
	/**
	 * Determines whether a given option was passed in the source.
	 *
	 * @param      string The option name.
	 *
	 * @return     bool True if the option exists in the array of passed
	 *                  arguments; false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasPassedOption($name)
	{
		return isset($this->passedOptions[(string)$name]);
	}
	
	/**
	 * Retrieves a given option from the parsed source data.
	 *
	 * @param      string The option name.
	 *
	 * @return     array Details about the option, including its arguments; null
	 *                   if the option does not exist in the parsed data.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getPassedOption($name)
	{
		return $this->hasPassedOption($name) ? $this->passedOptions[(string)$name] : null;
	}
	
	/**
	 * Adds a new option to the list of parseable options.
	 *
	 * @param      string A unique identifier for the option.
	 * @param      array The short names (often characters) that identify the
	 *                   option in source data.
	 * @param      array The long names that identify the option in source data.
	 * @param      int The number of options that this option expects.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addOption($name, array $shortNames, array $longNames, $handler, $arguments = 0)
	{
		$this->options[(string)$name] = array(
			'short_names' => $shortNames,
			'long_names' => $longNames,
			'handler' => $handler,
			'arguments' => $arguments
		);
	}
	
	/**
	 * Retrieves the list of all possible parseable options.
	 *
	 * @return     array This option parser's available options.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getOptions()
	{
		return $this->options;
	}
}

?>
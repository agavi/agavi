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
 * AgaviValidatorConfigHandler allows you to register validators with the
 * system.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviValidatorConfigHandler extends AgaviConfigHandler
{
	/**
	 * @var        array operator => validator mapping
	 */
	protected $classMap = array();

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		$this->classMap = array(
			'and' => array('class' => 'AgaviAndoperatorValidator', 'parameters' => array('break' => '1')),
			'datetime' => array('class' => 'AgaviDateTimeValidator', 'parameters' => array('check' => '1')),
			'email' => array('class' => 'AgaviEmailValidator', 'parameters' => array()),
			'equals' => array('class' => 'AgaviEqualsValidator', 'parameters' => array()),
			'file' => array('class' => 'AgaviFileValidator', 'parameters' => array()),
			'imagefile' => array('class' => 'AgaviImageFileValidator', 'parameters' => array()),
			'inarray' => array('class' => 'AgaviInarrayValidator', 'parameters' => array('sep' => ',')),
			'isset' => array('class' => 'AgaviIssetValidator', 'parameters' => array()),
			'isnotempty' => array('class' => 'AgaviIsNotEmptyValidator', 'parameters' => array()),
			'not' => array('class' => 'AgaviNotoperatorValidator', 'parameters' => array()),
			'number' => array('class' => 'AgaviNumberValidator', 'parameters' => array('type' => 'int')),
			'or' => array('class' => 'AgaviOroperatorValidator', 'parameters' => array('break' => '1')),
			'regex' => array('class' => 'AgaviRegexValidator', 'parameters' => array('match' => '1')),
			'set' => array('class' => 'AgaviSetValidator', 'parameters' => array()),
			'string' => array('class' => 'AgaviStringValidator', 'parameters' => array('min' => '1')),
			'xor' => array('class' => 'AgaviXoroperatorValidator', 'parameters' => array()),
		);

		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, true, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		$code = array();//array('lines' => array(), 'order' => array());

		foreach($configurations as $cfg) {
			if(isset($cfg->validator_definitions)) {
				foreach($cfg->validator_definitions as $vDev) {
					$name = $vDev->getAttribute('name');
					if(!isset($this->classMap[$name])) {
						$this->classMap[$name] = array('class' => $vDev->getAttribute('class'), 'parameters' => array());
					}
					$this->classMap[$name]['class'] = $vDev->getAttribute('class',$this->classMap[$name]['class']);
					$this->classMap[$name]['parameters'] = $this->getItemParameters($vDev, $this->classMap[$name]['parameters']);
				}
			}

			if(isset($cfg->validators)) {
				$hasValidators = false;
				foreach($cfg->getChildren() as $validators) {
					if($validators->getName() == 'validators') {
						$hasValidators = true;
						$stdSeverity = $validators->getAttribute('severity', 'error');
						$stdMethod = $validators->getAttribute('method');
						foreach($validators as $validator) {
							$code = $this->getValidatorArray($validator, $code, $stdSeverity, 'validationManager', $stdMethod);
						}
					}
				}
				if(!$hasValidators) {
					foreach($cfg->validators as $validator) {
						$code = $this->getValidatorArray($validator, $code, 'error', 'validationManager', null);
					}
				}
			}
		}

		$newCode = array();
		if(isset($code[''])) {
			$newCode = $code[''];
			unset($code['']);
		}

		foreach($code as $method => $codes) {
			$newCode[] = 'if($method == ' . var_export($method, true) . ') {';
			foreach($codes as $line) {
				$newCode[] = $line;
			}
			$newCode[] = '}';
		}

		return $this->generate($newCode);
	}

	/**
	 * Builds an array of php code strings, each of them creating a validator
	 *
	 * @param      AgaviConfigValueHolder The value holder of this validator.
	 * @param      array  The code of old validators (we simply overwrite "old" 
	 *                    validators here).
	 * @param      string The severity of the parent container.
	 * @param      string The name of the parent container.
	 * @param      string The method of the parent container.
	 * @param      bool Whether the parent container is required.
	 *
	 * @return     array php code blocks that register the validators
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getValidatorArray($validator, $code, $stdSeverity, $parent, $stdMethod, $stdRequired = true)
	{
		if(!isset($this->classMap[$validator->getAttribute('class')])) {
			$class = $validator->getAttribute('class');
			if(!class_exists($class)) {
				throw new AgaviValidatorException('unknown validator found: ' . $class);
			}
			$this->classMap[$class] = array('class' => $class, 'parameters' => array());
		} else {
			$class = $this->classMap[$validator->getAttribute('class')]['class'];
		}

		// setting up parameters
		$parameters = array(
			'severity' => $validator->getAttribute('severity', $stdSeverity),
			'required' => $stdRequired,
		);

		$arguments = array();
		$errors = array();

		$stdMethod = $validator->getAttribute('method', $stdMethod);
		$stdSeverity = $parameters['severity'];
		if($validator->hasAttribute('name')) {
			$name = $validator->getAttribute('name');
		} else {
			$name = AgaviToolkit::uniqid();
			$validator->setAttribute('name', $name);
		}

		$parameters = array_merge($this->classMap[$validator->getAttribute('class')]['parameters'], $parameters);
		$parameters = array_merge($parameters, $validator->getAttributes());
		$parameters = $this->getItemParameters($validator, $parameters);
		if(isset($validator->arguments)) {
			if($validator->arguments->hasAttribute('base')) {
				$parameters['base'] = $validator->arguments->getAttribute('base');
			}
			$args = array();
			foreach($validator->arguments as $argument) {
				if($argument->hasAttribute('name')) {
					$args[$argument->getAttribute('name')] = $argument->getValue();
				} else {
					$args[] = $argument->getValue();
				}
			}
			$arguments = $args;
		}
		if(isset($validator->errors)) {
			foreach($validator->errors as $error) {
				if($error->hasAttribute('for')) {
					$errors[$error->getAttribute('for')] = $error->getValue();
				} else {
					$errors[''] = $error->getValue();
				}
			}
		}
		if($validator->hasAttribute('required')) {
			$stdRequired = $parameters['required'] = AgaviToolkit::literalize($validator->getAttribute('required'));
		}

		$methods = array('');
		if(trim($stdMethod)) {
			$methods = preg_split('/[\s]+/', $stdMethod);
		}

		foreach($methods as $method) {
			$code[$method][$name] = implode("\n", array(
				sprintf(
					'${%s} = new %s();',
					var_export($name, true),
					$class
				),
				sprintf(
					'${%s}->initialize($this->getContext(), %s, %s, %s);',
					var_export($name, true),
					var_export($parameters, true),
					var_export($arguments, true),
					var_export($errors, true)
				),
				sprintf(
					'${%s}->addChild(${%s});',
					var_export($parent, true),
					var_export($name, true)
				),
			));
		}

		if(isset($validator->validators)) {
			$childSeverity = $validator->validators->getAttribute('severity', $stdSeverity);
			$childMethod = $validator->validators->getAttribute('method', $stdMethod);
			$childRequired = $stdRequired;
			if($validator->validators->hasAttribute('required')) {
				$childRequired = AgaviToolkit::literalize($validator->validators->getAttribute('required'));
			}
			foreach($validator->validators as $v) {
				$code = $this->getValidatorArray($v, $code, $childSeverity, $name, $childMethod, $childRequired);
			}
				// create child validators
		}

		return $code;
	}
}

?>
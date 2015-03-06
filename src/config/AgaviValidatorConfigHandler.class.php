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
class AgaviValidatorConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/validators/1.1';
	
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
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'validators');
		
		$config = $document->documentURI;
		
		$classMap = array();
		$code = array();//array('lines' => array(), 'order' => array());
		
		foreach($document->getConfigurationElements() as $cfg) {
			if($cfg->has('validator_definitions')) {
				foreach($cfg->get('validator_definitions') as $def) {
					$name = $def->getAttribute('name');
					if(!isset($this->classMap[$name])) {
						$this->classMap[$name] = array('class' => $def->getAttribute('class'), 'parameters' => array(), 'errors' => array());
					}
					$this->classMap[$name]['class'] = $def->getAttribute('class',$this->classMap[$name]['class']);
					$this->classMap[$name]['parameters'] = $def->getAgaviParameters($this->classMap[$name]['parameters']);
					$this->classMap[$name]['errors'] = $this->getAgaviErrors($def, $this->classMap[$name]['errors']);
				}
			}
			
			$code = $this->processValidatorElements($cfg, $code, 'validationManager');
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

		return $this->generate($newCode, $config);
	}

	/**
	 * Builds an array of php code strings, each of them creating a validator
	 *
	 * @param      AgaviXmlConfigDomElement The value holder of this validator.
	 * @param      array                    The code of old validators (we simply
	 *                                      overwrite "old" validators here).
	 * @param      string                   The name of the parent container.
	 * @param      string                   The severity of the parent container.
	 * @param      string                   The method of the parent container.
	 * @param      bool                     Whether parent container is required.
	 * @param      string                   The default translation domain of the parent container.
	 *
	 * @return     array PHP code blocks that register the validators
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Steffen Gransow <agavi@mivesto.de>
	 * @since      0.11.0
	 */
	protected function getValidatorArray($validator, $code, $parent, $stdSeverity, $stdMethod, $stdRequired = true, $stdTranslationDomain = null)
	{
		if(!isset($this->classMap[$validator->getAttribute('class')])) {
			$class = $validator->getAttribute('class');
			if(!class_exists($class)) {
				throw new AgaviValidatorException('unknown validator found: ' . $class);
			}
			$this->classMap[$class] = array('class' => $class, 'parameters' => array(), 'errors' => array());
		} else {
			$class = $this->classMap[$validator->getAttribute('class')]['class'];
		}
		
		// setting up parameters
		$parameters = array(
			'severity' => $validator->getAttribute('severity', $stdSeverity),
			'required' => $stdRequired,
		);
		
		$arguments = array();
		
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
		$parameters = $validator->getAgaviParameters($parameters);
		if(!array_key_exists('translation_domain', $parameters) && $stdTranslationDomain !== null) {
			$parameters['translation_domain'] = $stdTranslationDomain;
		} elseif(isset($parameters['translation_domain']) && $parameters['translation_domain'] === '') {
			// empty translation domains are forbidden, treat as if translation_domain was not set
			unset($parameters['translation_domain']);
		}
		
		foreach($validator->get('arguments') as $argument) {
			if($argument->hasAttribute('name')) {
				$arguments[$argument->getAttribute('name')] = $argument->getValue();
			} else {
				$arguments[] = $argument->getValue();
			}
		}
		
		if($validator->hasChild('arguments')) {
			$parameters['base'] = $validator->getChild('arguments')->getAttribute('base');
			
			if(!$arguments) {
				// no arguments defined, but there is an <arguments /> element, so we're validating an array there
				// lets add an empty fake argument for validation to work
				// must be an empty string, not null
				$arguments[] = '';
			}
		}
		
		$errors = $this->getAgaviErrors($validator, $this->classMap[$validator->getAttribute('class')]['errors']);
		
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
					var_export('_validator_' . $name, true),
					$class
				),
				sprintf(
					'${%s}->initialize($this->getContext(), %s, %s, %s);',
					var_export('_validator_' . $name, true),
					var_export($parameters, true),
					var_export($arguments, true),
					var_export($errors, true)
				),
				sprintf(
					'${%s}->addChild(${%s});',
					var_export($parent, true),
					var_export('_validator_' . $name, true)
				),
			));
		}
		
		// more <validator> or <validators> children
		$code = $this->processValidatorElements($validator, $code, '_validator_' . $name, $stdSeverity, $stdMethod, $stdRequired, isset($parameters['translation_domain']) ? $parameters['translation_domain'] : null);
		
		return $code;
	}
	
	/**
	 * Grabs generated code from the given element.
	 *
	 * @see        AgaviValidatorConfigHandler::getValidatorArray()
	 *
	 * @param      AgaviXmlConfigDomElement The value holder of this validator.
	 * @param      array                    The code of old validators (we simply
	 *                                      overwrite "old" validators here).
	 * @param      string                   The severity of the parent container.
	 * @param      string                   The name of the parent container.
	 * @param      string                   The method of the parent container.
	 * @param      bool                     Whether parent container is required.
	 * @param      string                   The default translation domain of the parent container.
	 *
	 * @return     array PHP code blocks that register the validators
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Steffen Gransow <agavi@mivesto.de>
	 * @since      0.11.0
	 */
	protected function processValidatorElements($node, $code, $name, $defaultSeverity = 'error', $defaultMethod = null, $defaultRequired = true, $defaultTranslationDomain = null)
	{
		// the problem here is that the <validators> parent is not just optional, but can also occur more than once
		foreach($node->get('validators') as $validator) {
			// let's see if this buddy has a <validators> parent with valuable information
			if($validator->parentNode->localName == 'validators') {
				$severity = $validator->parentNode->getAttribute('severity', $defaultSeverity);
				$method = $validator->parentNode->getAttribute('method', $defaultMethod);
				$translationDomain = $validator->parentNode->getAttribute('translation_domain', $defaultTranslationDomain);
			} else {
				$severity = $defaultSeverity;
				$method = $defaultMethod;
				$translationDomain = $defaultTranslationDomain;
			}
			$required = $defaultRequired;
			
			// append the code to generate
			$code = $this->getValidatorArray($validator, $code, $name, $severity, $method, $required, $translationDomain);
		}
		
		return $code;
	}
	
	/**
	 * Retrieve all of the Agavi error elements associated with this
	 * element.
	 *
	 * @param      AgaviXmlConfigDomElement The value holder of this validator.
	 * @param      array                    An array of existing errors.
	 *
	 * @return     array The complete array of errors.
	 *
	 * @author     Jan Schütze <JanS@DracoBlue.de>
	 * @author     Steffen Gransow <agavi@mivesto.de>
	 *
	 * @since      1.0.8
	 */
	public function getAgaviErrors(AgaviXmlConfigDomElement $node, array $existing = array())
	{
		$result = $existing;
		
		$elements = $node->get('errors', self::XML_NAMESPACE);
		
		foreach($elements as $element) {
			$key = '';
			if($element->hasAttribute('for')) {
				$key = $element->getAttribute('for');
			}
			
			$result[$key] = $element->getValue();
		}
		
		return $result;
	}
}

?>

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
 * AgaviTranslationConfigHandler allows you to define translator implementations
 * for different domains.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviTranslationConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/translation/1.0';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitextender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'translation');
		
		$config = $document->documentURI;
		
		$translatorData = array();
		$localeData = array();

		$defaultDomain = '';
		$defaultLocale = null;
		$defaultTimeZone = null;

		foreach($document->getConfigurationElements() as $cfg) {

			if($cfg->hasChild('available_locales')) {
				$availableLocales = $cfg->getChild('available_locales');
				// TODO: is this really optional? according to the schema: yes...
				$defaultLocale = $availableLocales->getAttribute('default_locale', $defaultLocale);
				$defaultTimeZone = $availableLocales->getAttribute('default_timezone', $defaultTimeZone);
				foreach($availableLocales as $locale) {
					$name = $locale->getAttribute('identifier');
					if(!isset($localeData[$name])) {
						$localeData[$name] = array('name' => $name, 'params' => array(), 'fallback' => null, 'ldml_file' => null);
					}
					$localeData[$name]['params'] = $locale->getAgaviParameters($localeData[$name]['params']);
					$localeData[$name]['fallback'] = $locale->getAttribute('fallback', $localeData[$name]['fallback']);
					$localeData[$name]['ldml_file'] = $locale->getAttribute('ldml_file', $localeData[$name]['ldml_file']);
				}
			}

			if($cfg->hasChild('translators')) {
				$translators = $cfg->getChild('translators');
				$defaultDomain = $translators->getAttribute('default_domain', $defaultDomain);
				$this->getTranslators($translators, $translatorData);
			}
		}

		$data = array();

		$data[] = sprintf('$this->defaultDomain = %s;', var_export($defaultDomain, true));
		$data[] = sprintf('$this->defaultLocaleIdentifier = %s;', var_export($defaultLocale, true));
		$data[] = sprintf('$this->defaultTimeZone = %s;', var_export($defaultTimeZone, true));

		foreach($localeData as $locale) {
			// TODO: fallback stuff

			$data[] = sprintf('$this->availableConfigLocales[%s] = array(\'identifier\' => %s, \'identifierData\' => %s, \'parameters\' => %s);', var_export($locale['name'], true), var_export($locale['name'], true), var_export(AgaviLocale::parseLocaleIdentifier($locale['name']), true), var_export($locale['params'], true));
		}

		foreach($translatorData as $domain => $translator) {
			foreach(array('msg', 'num', 'cur', 'date') as $type) {
				if(isset($translator[$type]['class'])) {
					if(!class_exists($translator[$type]['class'])) {
						throw new AgaviConfigurationException(sprintf('The Translator or Formatter class "%s" for domain "%s" could not be found.', $translator[$type]['class'], $domain));
					}
					$data[] = join("\n", array(
						sprintf('$this->translators[%s][%s] = new %s();', var_export($domain, true), var_export($type, true), $translator[$type]['class']),
						sprintf('$this->translators[%s][%s]->initialize($this->getContext(), %s);', var_export($domain, true), var_export($type, true), var_export($translator[$type]['params'], true)),
						sprintf('$this->translatorFilters[%s][%s] = %s;', var_export($domain, true), var_export($type, true), var_export($translator[$type]['filters'], true)),
					));
				}
			}
		}

		return $this->generate($data, $config);
	}
	
	/**
	 * Builds a list of filters for a translator.
	 *
	 * @param      AgaviConfigValueHolder The Translator node.
	 *
	 * @return     array An array of filter definitions.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getFilters($translator)
	{
		$filters = array();
		if($translator->has('filters')) {
			foreach($translator->get('filters') as $filter) {
				$func = explode('::', $filter->getValue());
				if(count($func) != 2) {
					$func = $func[0];
				}
				if(!is_callable($func)) {
					throw new AgaviConfigurationException('Non-existant or uncallable filter function "' . $filter->getValue() .  '" specified.');
				}
				$filters[] = $func;
			}
		}
		return $filters;
	}

	/**
	 * Build a list of translators.
	 *
	 * @param      AgaviConfigValueHolder The translators container.
	 * @param      array                  The destination data array.
	 * @param      string                 The name of the parent domain.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getTranslators($translators, &$data, $parent = null)
	{
		static $defaultData = array(
			'msg'  => array('class' => null, 'filters' => array(), 'params' => array()),
			'num'  => array('class' => 'AgaviNumberFormatter', 'filters' => array(), 'params' => array()),
			'cur'  => array('class' => 'AgaviCurrencyFormatter', 'filters' => array(), 'params' => array()),
			'date' => array('class' => 'AgaviDateFormatter', 'filters' => array(), 'params' => array()),
		);

		foreach($translators as $translator) {
			$domain = $translator->getAttribute('domain');
			if($parent) {
				$domain = $parent . '.' . $domain;
			}
			if(!isset($data[$domain])) {
				if(!$parent) {
					$data[$domain] = $defaultData;
				} else {
					$data[$domain] = array();
				}
			}

			$domainData =& $data[$domain];

			foreach(array('msg' => 'message_translator', 'num' => 'number_formatter', 'cur' => 'currency_formatter', 'date' => 'date_formatter') as $type => $nodeName) {
				if($translator->hasChild($nodeName)) {
					$node = $translator->getChild($nodeName);
					if(!isset($domainData[$type])) {
						$domainData[$type] = $defaultData[$type];
					}
					
					if($node->hasAttribute('translation_domain')) {
						$domainData[$type]['params']['translation_domain'] = $node->getAttribute('translation_domain');
					}
					$domainData[$type]['class'] = $node->getAttribute('class', $domainData[$type]['class']);
					$domainData[$type]['params'] = $node->getAgaviParameters($domainData[$type]['params']);
					$domainData[$type]['filters'] = $this->getFilters($node);
				}
			}

			if($translator->has('translators')) {
				$this->getTranslators($translator->get('translators'), $data, $domain);
			}
		}
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviTranslationConfigHandler extends AgaviConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviConfigurationException</b> on error in the config.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		$translatorData = array();
		$localeData = array();

		$defaultDomain = '';
		$defaultLocale = null;
		$defaultTimeZone = null;

		foreach($configurations as $cfg) {

			if(isset($cfg->available_locales)) {
				$defaultLocale = $cfg->available_locales->getAttribute('default_locale', $defaultLocale);
				$defaultTimeZone = $cfg->available_locales->getAttribute('default_timezone', $defaultTimeZone);
				foreach($cfg->available_locales as $locale) {
					$name = $locale->getAttribute('identifier');
					if(!isset($localeData[$name])) {
						$localeData[$name] = array('name' => $name, 'params' => array(), 'fallback' => null, 'ldml_file' => null);
					}
					$localeData[$name]['params'] = $this->getItemParameters($locale, $localeData[$name]['params']);
					$localeData[$name]['fallback'] = $locale->getAttribute('fallback', $localeData[$name]['fallback']);
					$localeData[$name]['ldml_file'] = $locale->getAttribute('ldml_file', $localeData[$name]['ldml_file']);
				}
			}

			if(isset($cfg->translators)) {
				$defaultDomain = $cfg->translators->getAttribute('default_domain', $defaultDomain);
				foreach($cfg->translators as $translator) {
					$domain = $translator->getAttribute('domain');
					if(!isset($translatorData[$domain])) {
						$translatorData[$domain] = array(
							'msg'  => array('type' => null, 'filters' => array(), 'params' => array()),
							'num'  => array('type' => 'number', 'filters' => array(), 'params' => array()),
							'cur'  => array('type' => 'currency', 'filters' => array(), 'params' => array()),
							'date' => array('type' => 'date', 'filters' => array(), 'params' => array()),
						);
					}
					$domainData =& $translatorData[$domain];

					if(isset($translator->message_translator)) {
						$domainData['msg']['type']   = $translator->message_translator->getAttribute('type', $domainData['msg']['type']);
						$domainData['msg']['params'] = $this->getItemParameters($translator->message_translator, $domainData['msg']['params']);
						if(isset($translator->message_translator->filters)) {
							foreach($translator->message_translator->filters as $filter) {
								$func = explode('::', $filter->getValue());
								if(count($func) != 2) {
									$func = $func[0];
								}
								if(!is_callable($func)) {
									throw new AgaviConfigurationException('Non-existant or uncallable filter function "' . $filter->getValue() .  '" specified.');
								}
								$domainData['msg']['filters'][] = $func;
							}
						}
					}

					if(isset($translator->number_formatter)) {
						$domainData['num']['type']   = $translator->number_formatter->getAttribute('type', $domainData['num']['type']);
						$domainData['num']['params'] = $this->getItemParameters($translator->number_formatter, $domainData['num']['params']);
					}

					if(isset($translator->currency_formatter)) {
						$domainData['cur']['type']   = $translator->currency_formatter->getAttribute('type', $domainData['cur']['type']);
						$domainData['cur']['params'] = $this->getItemParameters($translator->currency_formatter, $domainData['cur']['params']);
					}

					if(isset($translator->date_formatter)) {
						$domainData['date']['type']   = $translator->date_formatter->getAttribute('type', $domainData['date']['type']);
						$domainData['date']['params'] = $this->getItemParameters($translator->date_formatter, $domainData['date']['params']);
					}
				}
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
			if(isset($translator['msg'])) {
				$data[] = $this->getInitializationCode($domain, 'msg', 'Agavi%sTranslator', $translator['msg']);
			}

			if(isset($translator['num'])) {
				$data[] = $this->getInitializationCode($domain, 'num', 'Agavi%sFormatter', $translator['num']);
			}

			if(isset($translator['cur'])) {
				$data[] = $this->getInitializationCode($domain, 'cur', 'Agavi%sFormatter', $translator['cur']);
			}

			if(isset($translator['date'])) {
				$data[] = $this->getInitializationCode($domain, 'date', 'Agavi%sFormatter', $translator['date']);
			}
		}

		// compile data
		$retval = "<?php\n" .
							"// auto-generated by ".__CLASS__."\n" .
							"// date: %s GMT\n%s\n?>";
		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), implode("\n", $data));

		return $retval;

	}

	/**
	 * Tries to resolve the given class name. This first checks whether a class
	 * given in $iface exists and if not whether one using the given format 
	 * exists.
	 *
	 * @param      string The format string given to sprintf.
	 * @param      string The class name we're looking for.
	 * @param      string The domain in which this translator is specified. This
	 *                    parameter is only used in the exception to ease 
	 *                    debugging your configuration.
	 *
	 * @return     string Data class name.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If the class couldn't be 
	 *                                                resolved.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getCustomClassName($format, $iface, $domain)
	{
		if(!class_exists($iface) && class_exists(sprintf($format, ucfirst($iface)))) {
			$iface = sprintf($format, ucfirst($iface));
		} elseif(!class_exists($iface)) {
			$err = sprintf('The translator for the domain specifies an unknown translator "%s" for the domain "%s"', $iface, $domain);
			throw new AgaviConfigurationException($err);
		}

		return $iface;
	}

	/**
	 * Generates the initialization code for the given translator in the given 
	 * domain and returns it.
	 *
	 * @param      string The domain.
	 * @param      string The type of the translator (msg|num|cur|date)
	 * @param      string The format of the class names.
	 * @param      array  The translator data.
	 *
	 * @return     string The code for type in the domain.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getInitializationCode($domain, $type, $typeFormat, $data)
	{
		$code = '';

		$params = $data['params'];
		$filters = $data['filters'];

		$iface = $this->getCustomClassName($typeFormat, $data['type'], $domain);

		$code .= sprintf('$this->translators[%s][%s] = new %s();', var_export($domain, true), var_export($type, true), $iface);
		$code .= sprintf('$this->translators[%s][%s]->initialize($this->getContext(), %s);', var_export($domain, true), var_export($type, true), var_export($params, true));
		$code .= sprintf('$this->translatorFilters[%s][%s] = %s;', var_export($domain, true), var_export($type, true), var_export($filters, true));

		return $code;
	}

}

?>
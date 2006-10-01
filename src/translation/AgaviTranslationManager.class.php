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
 * The translation manager manages the interface between the application and the
 * current translation engine implementation
 *
 * @package    agavi
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviTranslationManager
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        array An array of the translator instances for the domains.
	 */
	protected $translators = null;

	/**
	 * @var        AgaviLocale The current locale.
	 */
	protected $defaultLocale = null;

	/**
	 * @var        string The default domain which shall be used for translation.
	 */
	protected $defaultDomain = null;

	/**
	 * @var        array The available locales which have been defined in the 
	                     translation.xml config file.
	 */
	protected $availableConfigLocales = array();

	/**
	 * @var        array All available locales.
	 */
	protected $availableLocales = array();

	/**
	 * @var        array The supplemental data from the cldr
	 */
	protected $supplementalData;

	/**
	 * Initialize this TranslationManager.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/translation.xml'));
		$this->loadSupplementalData();
		$this->loadAvailableLocales();
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Gets called when the locale of the request has changed.
	 *
	 * This method will call the localeChanged() callback of every assigned 
	 * AgaviITranslator and stores the locale as default locale for each 
	 * translation request.
	 *
	 * @param      AgaviLocale The locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function localeChanged(AgaviLocale $locale)
	{
		if(!($locale instanceof AgaviLocale)) {
			if(!isset($this->availableLocales[$locale])) {
				throw new AgaviException('Trying to select unknown locale "' . $locale . '"');
			}
			$locale = $this->availableLocales[$locale];
		}

		if($locale != $this->defaultLocale) {
			$this->defaultLocale = $locale;
			foreach($this->translators as $translatorList) {
				foreach($translatorList as $translator) {
					$translator->localeChanged($locale);
				}
			}
		}
	}

	/**
	 * Retrieve the default locale.
	 *
	 * @return     AgaviLocale The default locale identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}

	/**
	 * Sets the default domain.
	 *
	 * @param      string The new default domain.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setDefaultDomain($domain)
	{
		$this->defaultDomain = $domain;
	}

	/**
	 * Retrieve the default domain.
	 *
	 * @return     string The default domain.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultDomain()
	{
		return $this->defaultDomain;
	}

	/**
	 * Formats a currency in the current locale.
	 *
	 * @param      mixed The number to be formatted.
	 *
	 * @return     string The formatted number.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function _c($number, $domain = null, $locale = null)
	{
		if($domain === null) {
			$domain = $this->defaultDomain;
		}

		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra);

		return $translator['cur']->translate($number, $domainExtra, $locale);
	}

	/**
	 * Formats a number in the current locale.
	 *
	 * @param      mixed The number to be formatted.
	 *
	 * @return     string The formatted number.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function _n($number, $domain = null, $locale = null)
	{
		if($domain === null) {
			$domain = $this->defaultDomain;
		}

		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra);

		return $translator['num']->translate($number, $domainExtra, $locale);
	}


	/**
	 * Translate a message into the current locale.
	 *
	 * @param      string The message.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function _($message, $domain = null, $locale = null, array $parameters = null)
	{
		if($domain === null) {
			$domain = $this->defaultDomain;
		}

		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra);

		$translatedMessage = $translator['msg']->translate($message, $domainExtra, $locale);
		if(is_array($parameters)) {
			$translatedMessage = vsprintf($translatedMessage, $parameters);
		}

		return $translatedMessage;
	}

	/**
	 * Returns the translators for a given domain.
	 *
	 * @param      string The domain.
	 * @param      string The remaining part in the domain which didn't match
	 *
	 * @return     array An array of translators for the given domain
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getTranslators($domain, &$domainExtra)
	{
		$domainParts = explode('.', $domain, 2);
		$translatorDomain = $domainParts[0];
		$domainExtra = isset($domainParts[1]) ? $domainParts[1] : '';

		if(isset($this->translators[$translatorDomain])) {
			return $this->translators[$translatorDomain];
		} else {
			// TODO: select proper exception type
			throw new AgaviException(sprintf('No translator exists for the domain "%s"', $translatorDomain));
		}
	}

	/**
	 * Loads the available locales into the instance variable
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadAvailableLocales()
	{
		$this->availableLocales = $this->availableConfigLocales;
	}

	/**
	 * Loads the supplemental data into the instance variable
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadSupplementalData()
	{
		$this->supplementalData = include(AgaviConfigCache::checkConfig(AgaviConfig::get('core.cldr_dir') . '/supplementalData.xml'));
	}


	/**
	 * Returns a new AgaviLocale object from the given identifier.
	 *
	 * @param      string The locale identifier
	 *
	 * @return     AgaviLocale The locale instance which matches the available
	 *                         locales most.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLocaleFromIdentifier($identifier)
	{
		static $dataCache = array();

		if(!isset($dataCache[$identifier])) {
			$idData = AgaviLocale::parseLocaleIdentifier($identifier);

			// if a locale with the given identifier doesn't exist try to find the closest
			// match or bail out on no match or an ambigious match
			if(isset($this->availableLocales[$identifier])) {
				$availableLocale = $this->availableLocales[$identifier];
			} else {
				$comparisons = array();
				if($idData['language']) {
					$comparisons[] = sprintf('%s == $a["identifierData"]["language"]', var_export($idData['language'], true));
				}
				if($idData['script']) {
					$comparisons[] = sprintf('%s == $a["identifierData"]["script"]', var_export($idData['script'], true));
				}
				if($idData['territory']) {
					$comparisons[] = sprintf('%s == $a["identifierData"]["territory"]', var_export($idData['territory'], true));
				}
				if($idData['variant']) {
					$comparisons[] = sprintf('%s == $a["identifierData"]["variant"]', var_export($idData['variant'], true));
				}
				/*
				if(count($idData['options'])) {
					$comparisons[] = sprintf('count(array_diff(%s, (array)$a["identifierData"]["options"])) == 0', var_export($idData['options'], true));
				}*/

				$code = sprintf('return (%s);', implode(' && ', $comparisons));

				$matchingLocales = array_filter($this->availableLocales, create_function('$a', $code));
				switch(count($matchingLocales)) {
					case 1:
						$availableLocale = current($matchingLocales);
						break;
					case 0:
						throw new AgaviException('Specified locale identifier ' . $identifier . ' which has no matching available locale defined');
					default:
						$matchedNames = array();
						foreach($matchingLocales as $matchedLocale) {
							$matchedNames[] = $matchedLocale['identifier'];
						}
						throw new AgaviException('Specified ambigious locale identifier ' . $identifier . ' which has matches: ' . implode(', ', $matchedNames));
				}
			}

			// when the user wants all options reset he supplies an 'empty' option set (identifier ends with @)
			if(substr($identifier, -1) == '@') {
				$availableLocale['identifierData']['options'] = array();
			} else {
				$availableLocale['identifierData']['options'] = array_merge($availableLocale['identifierData']['options'], $idData['options']);
			}
			$idData = $availableLocale['identifierData'];

			$lookupPath = AgaviLocale::getLookupPath($idData);
			$cldrDir = AgaviConfig::get('core.cldr_dir');
			$data = null;

			foreach($lookupPath as $localeName) {
				$fileName = $cldrDir . '/locales/' . $localeName . '.xml';
				if(is_readable($fileName)) {
					$data = include(AgaviConfigCache::checkConfig($fileName));
					break;
				}
			}
			if($data === null) {
				throw new AgaviException('No data available for locale ' . $identifier);
			}

			if($idData['territory']) {
				$territory = $idData['territory'];
				if(isset($this->supplementalData['territories'][$territory]['currencies'])) {
					$currency = current(array_slice($this->supplementalData['territories'][$territory]['currencies'], 0, 1));
					$data['locale']['currency'] = $currency['currency'];
				}
			}

			if(isset($idData['options']['calendar'])) {
				$data['locale']['calendar'] = $idData['options']['calendar'];
			}

			if(isset($idData['options']['currency'])) {
				$data['locale']['currency'] = $idData['options']['currency'];
			}

			if(isset($idData['options']['timezone'])) {
				$data['locale']['timezone'] = $idData['options']['timezone'];
			}

			$dataCache[$identifier] = array_merge_recursive($data, $availableLocale['parameters']);
		}

		$data = $dataCache[$identifier];
		$locale = new AgaviLocale();
		$locale->initialize(null, $identifier, $data);

		return $locale;
	}
}

?>
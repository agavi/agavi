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
	protected $currentLocale = null;

	/**
	 * @var        string The original locale identifier given to this instance.
	 */
	protected $givenLocaleIdentifier = null;

	/**
	 * @var        string The identifier of the current locale.
	 */
	protected $currentLocaleIdentifier = null;

	/**
	 * @var        string The current locale.
	 */
	protected $defaultLocaleIdentifier = null;


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
	 * @var        array All available locales. Just stores the info for lazyload.
	 */
	protected $availableLocales = array();

	/**
	 * @var        array A cache for the data of the available locales.
	 */
	protected $localeDataCache = array();

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
	 * Returns the list of available locales.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAvailableLocales()
	{
		return $this->availableLocales;
	}


	/**
	 * Sets the current locale.
	 *
	 * @param      string The locale identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setLocale($identifier)
	{
		$this->givenLocaleIdentifier = $identifier;
		$this->currentLocaleIdentifier = $this->getClosestMatchingLocale($identifier);
	}

	/**
	 * Retrieve the current locale.
	 *
	 * @return     AgaviLocale The current locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrentLocale()
	{
		$this->loadCurrentLocale();
		return $this->currentLocale;
	}

	/**
	 * Retrieve the current locale identifier. This may not necessarily match 
	 * what has be given to setLocale() but instead the identifier of the closest
	 * match from the available locales.
	 *
	 * @return     string The locale identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrentLocaleIdentifier()
	{
		if($this->currentLocaleIdentifier === null) {
			if($this->defaultLocaleIdentifier === null) {
				throw new AgaviException('Tried to use the translation system without a default locale and without a locale set');
			}
			$this->setLocale($this->defaultLocaleIdentifier);
		}
		return $this->currentLocaleIdentifier;
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

		if($locale === null) {
			$this->loadCurrentLocale();
		} elseif(is_string($locale)) {
			$locale = $this->getLocaleFromIdentifier($locale);
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

		if($locale === null) {
			$this->loadCurrentLocale();
		} elseif(is_string($locale)) {
			$locale = $this->getLocaleFromIdentifier($locale);
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
		
		if($locale === null) {
			$this->loadCurrentLocale();
		} elseif(is_string($locale)) {
			$locale = $this->getLocaleFromIdentifier($locale);
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
	 * Translate a singular/plural message into the current locale.
	 *
	 * @param      string The message.
	 *
	 * @return     string The translated message.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __($singularMessage, $pluralMessage, $amount, $domain = null, $locale = null, array $parameters = null)
	{
		return $this->_(array($singularMessage, $pluralMessage, $amount), $domain, $locale, $parameters);
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
	 * Lazy loads the current locale if necessary.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadCurrentLocale()
	{
		if(!$this->currentLocale || $this->currentLocale->getIdentifier() != $this->getCurrentLocaleIdentifier()) {
			$this->currentLocale = $this->getLocaleFromIdentifier($this->getCurrentLocaleIdentifier());
			foreach($this->translators as $translatorList) {
				foreach($translatorList as $translator) {
					$translator->localeChanged($this->currentLocale);
				}
			}
		}
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
	 * Returns the identifier of the available locale which matches the given 
	 * locale identifier most.
	 *
	 * @param      string The locale identifier
	 *
	 * @return     string The locale identifier of the available locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getClosestMatchingLocale($identifier)
	{

		// if a locale with the given identifier doesn't exist try to find the closest
		// match or bail out on no match or an ambigious match
		if(isset($this->availableLocales[$identifier])) {
			return $identifier;
		}

		$idData = AgaviLocale::parseLocaleIdentifier($identifier);
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
		
		return $availableLocale['identifier'];
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
		$idData = AgaviLocale::parseLocaleIdentifier($identifier);
		$availableLocale = $this->availableLocales[$this->getClosestMatchingLocale($identifier)];

		if(!isset($this->localeDataCache[$idData['locale_str']])) {
			$lookupPath = AgaviLocale::getLookupPath($availableLocale['identifierData']);
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

			if($availableLocale['identifierData']['territory']) {
				$territory = $availableLocale['identifierData']['territory'];
				if(isset($this->supplementalData['territories'][$territory]['currencies'])) {
					$currency = current(array_slice($this->supplementalData['territories'][$territory]['currencies'], 0, 1));
					$data['locale']['currency'] = $currency['currency'];
				}
			}

			$this->localeDataCache[$idData['locale_str']] = array_merge_recursive($data, $availableLocale['parameters']);
		}

		$data = $this->localeDataCache[$idData['locale_str']];

		// if the user wants all options reset he supplies an 'empty' option set (identifier ends with @)
		if(substr($identifier, -1) == '@') {
			$idData['options'] = array();
		} else {
			$idData['options'] = array_merge($availableLocale['identifierData']['options'], $idData['options']);
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

		if(($atPos = strpos($identifier, '@')) !== false) {
			$identifier = $availableLocale['identifierData']['locale_str'] . substr($identifier, $atPos);
		} else {
			$identifier = $availableLocale['identifier'];
		}

		$locale = new AgaviLocale();
		$locale->initialize($this->context, $identifier, $data);

		return $locale;
	}
}

?>
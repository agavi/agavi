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
 * The translation manager manages the interface between the application and the
 * current translation engine implementation
 *
 * @package    agavi
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviTranslationManager
{
	const MESSAGE = 'msg';
	const NUMBER = 'num';
	const CURRENCY = 'cur';
	const DATETIME = 'date';

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
	 * @var        string The default locale identifier.
	 */
	protected $defaultLocaleIdentifier = null;

	/**
	 * @var        string The default domain which shall be used for translation.
	 */
	protected $defaultDomain = null;

	/**
	 * @var        array The available locales which have been defined in the 
	 *                   translation.xml config file.
	 */
	protected $availableConfigLocales = array();

	/**
	 * @var        array All available locales. Just stores the info for lazyload.
	 */
	protected $availableLocales = array();

	/**
	 * @var        array A cache for locale instances.
	 */
	protected $localeCache = array();

	/**
	 * @var        array A cache for the data of the available locales.
	 */
	protected $localeDataCache = array();

	/**
	 * @var        array The supplemental data from the cldr
	 */
	protected $supplementalData;

	/**
	 * @var        array The list of available time zones.
	 */
	protected $timeZoneList = array();

	/**
	 * @var        array A cache for the time zone instances.
	 */
	protected $timeZoneCache = array();

	/**
	 * @var        string The default time zone. If not set the timezone php 
	 *                    will be used as default.
	 */
	protected $defaultTimeZone = null;

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
		$this->loadTimeZoneData();
		$this->loadAvailableLocales();
		if($this->defaultLocaleIdentifier === null) {
			throw new AgaviException('Tried to use the translation system without a default locale and without a locale set');
		}
		$this->setLocale($this->defaultLocaleIdentifier);
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
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
	 * @author     David Zülke <dz@bitxtender.com>
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
		$this->currentLocaleIdentifier = $this->getLocaleIdentifier($identifier);
		$givenData = AgaviLocale::parseLocaleIdentifier($identifier);
		$actualData = AgaviLocale::parseLocaleIdentifier($this->currentLocaleIdentifier);
		// construct the given name from the locale from the closest match and the options that were given to the requested locale identifer
		$this->givenLocaleIdentifier = $actualData['locale_str'] . $givenData['option_str'];
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
		return $this->currentLocaleIdentifier;
	}

	/**
	 * Retrieve the default locale.
	 *
	 * @return     AgaviLocale The current default.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultLocale()
	{
		$this->getLocale($this->defaultLocale);
	}

	/**
	 * Retrieve the default locale identifier.
	 *
	 * @return     string The default locale identifier.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultLocaleIdentifier()
	{
		return $this->defaultLocaleIdentifier;
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
	 * Formats a date in the current locale.
	 *
	 * @param      mixed       The date to be formatted.
	 * @param      string      The domain in which the date should be formatted.
	 * @param      AgaviLocale The locale which should be used for formatting.
	 *                         Defaults to the currently active locale.
	 *
	 * @return     string The formatted date.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function _d($date, $domain = null, $locale = null)
	{
		if($domain === null) {
			$domain = $this->defaultDomain;
		}

		if($locale === null) {
			$this->loadCurrentLocale();
		} elseif(is_string($locale)) {
			$locale = $this->getLocale($locale);
		}
		
		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra, self::DATETIME);

		$retval = $translator->translate($date, $domainExtra, $locale);
		
		$retval = $this->applyFilters($retval, $domain, self::DATETIME);
		
		return $retval;
	}

	/**
	 * Formats a currency amount in the current locale.
	 *
	 * @param      mixed       The number to be formatted.
	 * @param      string      The domain in which the amount should be formatted.
	 * @param      AgaviLocale The locale which should be used for formatting.
	 *                         Defaults to the currently active locale.
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
			$locale = $this->getLocale($locale);
		}
		
		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra, self::CURRENCY);

		$retval = $translator->translate($number, $domainExtra, $locale);
		
		$retval = $this->applyFilters($retval, $domain, self::CURRENCY);
		
		return $retval;
	}

	/**
	 * Formats a number in the current locale.
	 *
	 * @param      mixed       The number to be formatted.
	 * @param      string      The domain in which the number should be formatted.
	 * @param      AgaviLocale The locale which should be used for formatting.
	 *                         Defaults to the currently active locale.
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
			$locale = $this->getLocale($locale);
		}
		
		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra, self::NUMBER);

		$retval = $translator->translate($number, $domainExtra, $locale);
		
		$retval = $this->applyFilters($retval, $domain, self::NUMBER);
		
		return $retval;
	}

	/**
	 * Translate a message into the current locale.
	 *
	 * @param      mixed       The message.
	 * @param      string      The domain in which the translation should be done.
	 * @param      AgaviLocale The locale which should be used for formatting.
	 *                         Defaults to the currently active locale.
	 * @param      array       The parameters which should be used for sprintf on
	 *                         the translated string.
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
			$locale = $this->getLocale($locale);
		}
		
		$domainExtra = '';
		$translator = $this->getTranslators($domain, $domainExtra, self::MESSAGE);

		$retval = $translator->translate($message, $domainExtra, $locale);
		if(is_array($parameters)) {
			$retval = vsprintf($retval, $parameters);
		}
		
		$retval = $this->applyFilters($retval, $domain, self::MESSAGE);
		
		return $retval;
	}

	/**
	 * Translate a singular/plural message into the current locale.
	 *
	 * @param      string      The message for the singular form.
	 * @param      string      The message for the plural form.
	 * @param      int         The amount for which the translation should happen.
	 * @param      string      The domain in which the translation should be done.
	 * @param      AgaviLocale The locale which should be used for formatting.
	 *                         Defaults to the currently active locale.
	 * @param      array       The parameters which should be used for sprintf on
	 *                         the translated string.
	 *
	 * @return     string The translated message.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @param      string The type of the translator
	 *
	 * @return     array An array of translators for the given domain
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getTranslators(&$domain, &$domainExtra, $type = null)
	{
		if($domain[0] == '.') {
			$domain = $this->defaultDomain . $domain;
		}

		$domainParts = explode('.', $domain);
		$partCount = count($domainParts);
		$extraParts = array();

		do {
			if(count($domainParts) == 0) {
				throw new InvalidArgumentException(sprintf('No translator exists for the domain "%s"', $domain));
			}
			$td = implode('.', $domainParts);
			array_pop($domainParts);
		} while(!isset($this->translators[$td]) || ($type && !isset($this->translators[$td][$type])));

		$domainExtra = substr($domain, strlen($td) + 1);
		$domain = $td;
		return $type ? $this->translators[$td][$type] : $this->translators[$td];
	}

	/**
	 * Returns the translators for a given domain and type. The domain can contain
	 * any extra parts which will be ignored. Will return null when no tanslator 
	 * is defined.
	 *
	 * @param      string The domain.
	 * @param      string The type of the translator.
	 *
	 * @return     AgaviITranslator The translator instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDomainTranslator($domain, $type)
	{
		try {
			$domainExtra = '';
			return $this->getTranslators($domain, $domainExtra, $type);
		} catch(InvalidArgumentException $e) {
			return null;
		}
	}

	/**
	 * Returns the translator filters for a given domain.
	 *
	 * @param      string The message.
	 * @param      string The domain (w/o extra parts).
	 * @param      string The type.
	 *
	 * @return     string The new message.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function applyFilters($message, $domain, $type = self::MESSAGE)
	{
		if(isset($this->translatorFilters[$domain][$type])) {
			foreach($this->translatorFilters[$domain][$type] as $filter) {
				$message = call_user_func($filter, $message);
			}
		}
		
		return $message;
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
		if(!$this->currentLocale || $this->currentLocale->getIdentifier() != $this->givenLocaleIdentifier) {
			$this->currentLocale = $this->getLocale($this->givenLocaleIdentifier);
			// we first need to initialize all message translators before the number formatters
			foreach($this->translators as $translatorList) {
				foreach($translatorList as $type => $translator) {
					if($type == self::MESSAGE) {
						$translator->localeChanged($this->currentLocale);
					}
				}
			}
			foreach($this->translators as $translatorList) {
				foreach($translatorList as $type => $translator) {
					if($type != self::MESSAGE) {
						$translator->localeChanged($this->currentLocale);
					}
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
	 * Loads the time zone data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadTimeZoneData()
	{
		$this->timeZoneList = include(AgaviConfig::get('core.cldr_dir') . '/timezones/zonelist.php');
	}

	/**
	 * @see        AgaviTranslationManager::getLocaleIdentifier
	 *
	 * @deprecated Superseded by AgaviTranslationManager::getLocaleIdentifier()
	 */
	public function getClosestMatchingLocale($identifier)
	{
		return $this->getLocaleIdentifier($identifier);
	}
	
	/**
	 * Returns the identifier of the available locale which matches the given 
	 * locale identifier most.
	 *
	 * @param      string A locale identifier
	 *
	 * @return     string The actual locale identifier of the available locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLocaleIdentifier($identifier)
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
	 * @see        AgaviTranslationManager::getLocale
	 *
	 * @deprecated Superseded by AgaviTranslationManager::getLocale()
	 */
	public function getLocaleFromIdentifier($identifier)
	{
		return $this->getLocale($identifier);
	}
	
	/**
	 * Returns a new AgaviLocale object from the given identifier.
	 *
	 * @param      string The locale identifier
	 * @param      bool   Force a new instance even if an identical one exists.
	 *
	 * @return     AgaviLocale The locale instance which matches the available
	 *                         locales most.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLocale($identifier, $forceNew = false)
	{
		// enable shortcut notation to only set options to the current locale
		if($identifier[0] == '@' && $this->currentLocaleIdentifier) {
			$idData = AgaviLocale::parseLocaleIdentifier($this->currentLocaleIdentifier);
			$identifier = $idData['locale_str'] . $identifier;

			$newIdData = AgaviLocale::parseLocaleIdentifier($identifier);
			$idData['options'] = array_merge($idData['options'], $newIdData['options']);
		} else {
			$idData = AgaviLocale::parseLocaleIdentifier($identifier);
		}
		// this doesn't care about the options
		$availableLocale = $this->availableLocales[$this->getLocaleIdentifier($identifier)];

		// if the user wants all options reset he supplies an 'empty' option set (identifier ends with @)
		if(substr($identifier, -1) == '@') {
			$idData['options'] = array();
		} else {
			$idData['options'] = array_merge($availableLocale['identifierData']['options'], $idData['options']);
		}

		if(($atPos = strpos($identifier, '@')) !== false) {
			$identifier = $availableLocale['identifierData']['locale_str'] . substr($identifier, $atPos);
		} else {
			$identifier = $availableLocale['identifier'];
		}

		if(!$forceNew && isset($this->localeCache[$identifier])) {
			return $this->localeCache[$identifier];
		}

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
					$slice = array_slice($this->supplementalData['territories'][$territory]['currencies'], 0, 1);
					$currency = current($slice);
					$data['locale']['currency'] = $currency['currency'];
				}
			}

			$this->localeDataCache[$idData['locale_str']] = $data;
		}

		$data = $this->localeDataCache[$idData['locale_str']];

		if(isset($idData['options']['calendar'])) {
			$data['locale']['calendar'] = $idData['options']['calendar'];
		}

		if(isset($idData['options']['currency'])) {
			$data['locale']['currency'] = $idData['options']['currency'];
		}

		if(isset($idData['options']['timezone'])) {
			$data['locale']['timezone'] = $idData['options']['timezone'];
		}

		$locale = new AgaviLocale();
		$locale->initialize($this->context, $availableLocale['parameters'], $identifier, $data);

		if(!$forceNew) {
			$this->localeCache[$identifier] = $locale;
		}

		return $locale;
	}

	/**
	 * Sets the default time zone.
	 *
	 * @param      string The timezone identifier
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setDefaultTimeZone($id)
	{
		$this->defaultTimeZone = $id;
	}

	/**
	 * Gets the instance of the current timezone.
	 *
	 * @return     AgaviTimeZone The current timezone instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrentTimeZone()
	{
		if($this->defaultTimeZone !== null) {
			$tz = $this->defaultTimeZone;
		} else {
			$tz = date_default_timezone_get();
		}
		return $this->createTimeZone($tz);
	}

	/**
	 * Gets the territory id a (resolved) timezone id belongs to.
	 *
	 * @param      string The resolved timezone id.
	 * @param      bool   Will receive whether the territory has multiple 
	 *                    time zones
	 *
	 * @return     string The territory identifer or null.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTimeZoneTerritory($id, &$hasMultipleZones = false)
	{
		if(isset($this->supplementalData['timezones']['territories'][$id])) {
			$territory = $this->supplementalData['timezones']['territories'][$id];
			$hasMultipleZones = isset($this->supplementalData['timezones']['multiZones'][$territory]);
			return $territory;
		}

		return null;
	}

	/**
	 * Creates a new timezone instance for the given identifier.
	 *
	 * Please note that this method caches the results for each identifier, so
	 * if you plan to modify the timezones returned by this method you need to 
	 * clone them first. Alternatively you can set the cache parameter to false,
	 * but this will mean the data for this timezone will be loaded from the 
	 * hdd again.
	 *
	 * @return     AgaviTimeZone The timezone instance for the given id.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createTimeZone($id, $cache = true)
	{
		if(!isset($this->timeZoneList[$id])) {
			return AgaviTimeZone::createCustomTimeZone($this, $id);
		}

		if(!isset($this->timeZoneCache[$id]) || !$cache) {
			$currId = $id;

			// resolve links
			while($this->timeZoneList[$currId]['type'] == 'link') {
				$currId = $this->timeZoneList[$currId]['to'];
			}

			$zoneData = include(AgaviConfig::get('core.cldr_dir') . '/timezones/' . $this->timeZoneList[$currId]['filename']);

			$zone = new AgaviOlsonTimeZone($this, $id, $zoneData);
			$zone->setResolvedId($currId);
			if($cache) {
				$this->timeZoneCache[$id] = $zone;
			}
		} else {
			$zone = $this->timeZoneCache[$id];
		}

		return $zone; 
	}

	/**
	 * Creates a new calendar instance with the current time set.
	 *
	 * @param      mixed This can be either an AgaviLocale, an AgaviTimeZone or
	 *                   a string specifying the calendar type.
	 *
	 * @return     AgaviCalendar The current timezone instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createCalendar($type = null)
	{
		$locale = $this->getCurrentLocale();
		$calendarType = null;
		$zone = null;
		$time = null;
		if($type instanceof AgaviLocale) {
			$locale = $type;
		} elseif($type instanceof AgaviTimeZone) {
			$zone = $type;
		} elseif($type instanceof DateTime) {
			$time = $type;
		} elseif(is_int($type)) {
			$time = $type * AgaviDateDefinitions::MILLIS_PER_SECOND;
		} elseif($type !== null) {
			$calendarType = $type;
		}
		if($time === null) {
			$time = AgaviCalendar::getNow();
		}

		if(!$zone) {
			if($locale->getLocaleTimeZone()) {
				$zone = $this->createTimeZone($locale->getLocaleTimeZone());
			}
		}

		if(!$calendarType) {
			$calendarType = $locale->getLocaleCalendar();
			if(!$calendarType) {
				$calendarType = AgaviCalendar::GREGORIAN;
			}
		}

		switch($calendarType) {
			case AgaviCalendar::GREGORIAN:
				$c = new AgaviGregorianCalendar($this /* $locale */);
				break;
			default:
				throw new AgaviException('Calendar type ' . $calendarType . ' not supported');
		}

		// Now, reset calendar to default state:
		if($zone) {
			$c->setTimeZone($zone);
		}

		if($time instanceof DateTime) {
			$tzName = $time->getTimezone()->getName();
			if(preg_match('/^[+-0-9]/', $tzName)) {
				$tzName = 'GMT' . $tzName;
			}
			$c->setTimeZone($this->createTimeZone($tzName));
			$dateStr = $time->format('Y z G i s');
			list($year, $doy, $hour, $minute, $second) = explode(' ', $dateStr);
			$c->set(AgaviDateDefinitions::YEAR, $year);
			$c->set(AgaviDateDefinitions::DAY_OF_YEAR, $doy + 1);
			$c->set(AgaviDateDefinitions::HOUR_OF_DAY, $hour);
			$c->set(AgaviDateDefinitions::MINUTE, $minute);
			$c->set(AgaviDateDefinitions::SECOND, $second);

			// complete the calendar
			$c->getAll();
		} else {
			$c->setTime($time); // let the new calendar have the current time.
		}

		return $c;
	}

	/**
	 * Returns the stored information from the ldml supplemental data about a 
	 * territory.
	 *
	 * @param      string The uppercase 2 letter country iso code.
	 *
	 * @return     array The data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTerritoryData($country)
	{
		if(!isset($this->supplementalData['territories'][$country])) {
			return array();
		}
		return $this->supplementalData['territories'][$country];
	}

	/**
	 * Returns an array containing digits and rounding information for a currency.
	 *
	 * @param      string The uppercase 3 letter currency iso code.
	 *
	 * @return     array The data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrencyFraction($currency)
	{
		if(!isset($this->supplementalData['fractions'][$currency])) {
			return $this->supplementalData['fractions']['DEFAULT'];
		}
		return $this->supplementalData['fractions'][$currency];
	}
}

?>
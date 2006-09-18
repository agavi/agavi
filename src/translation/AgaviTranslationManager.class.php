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
	 * Initialize this TranslationManager.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		$this->context = $context;

		require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/translation.xml'));
		$this->retrieveAvailableLocales();
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
	 * @param      string The locale identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function localeChanged($locale)
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
	public function _($message, $domain = null, $locale = null, $parameters = null)
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

	protected function retrieveAvailableLocales()
	{
		$this->availableLocales = $this->availableConfigLocales;
	}
}

?>
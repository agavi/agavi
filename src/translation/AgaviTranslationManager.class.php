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
	 * @var        string The current locale.
	 */
	protected $defaultLocale = null;

	/**
	 * @var        string The default domain which shall be used for translation.
	 */
	protected $defaultDomain = null;

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

		require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/translators.xml'));

		$this->defaultLocale = isset($parameters['default_locale']) ? $parameters['default_locale'] : 'en_us';
		$this->defaultDomain = isset($parameters['default_domain']) ? $parameters['default_domain'] : '';
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
	 * Sets the default locale.
	 *
	 * @param      string The locale identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setDefaultLocale($locale)
	{
		$this->defaultLocale = $locale;
	}

	/**
	 * Retrieve the default locale.
	 *
	 * @return     string The default locale identifier.
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
		if($locale === null) {
			$locale = $this->defaultLocale;
		}
		if($domain === null) {
			$domain = $this->defaultDomain;
		}
		if($parameters === null) {
		}

		$domainParts = explode('.', $domain, 2);
		$translatorDomain = $domainParts[0];
		if(isset($this->translators[$translatorDomain])) {
			$translatedMessage = $this->translators[$translatorDomain]->translate($message, $domain, $locale);
			if(is_array($parameters)) {
				$translatedMessage = vsprintf($translatedMessage, $parameters);
			}
		} else {
			// TODO: select proper exception type
			throw new AgaviException(sprintf('No translator exists for the domain "%s"', $translatorDomain));
		}

		return $translatedMessage;
	}
}

?>
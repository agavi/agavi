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
 * AgaviSimpleTranslator defines the translator which loads the data from its
 * parameters.
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
class AgaviSimpleTranslator extends AgaviBasicTranslator
{
	/**
	 * @var        array The data for each domain
	 */
	protected $domainData = array();

	/**
	 * @var        array The data for the currently active locale
	 */
	protected $currentData = array();

	/**
	 * @var        AgaviLocale The currently set locale
	 */
	protected $locale = null;

	/**
	 * Initialize this Translator.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context);

		$domainData = array();

		foreach((array)$parameters as $domain => $locales) {
			foreach((array)$locales as $locale => $translations) {
				foreach((array)$translations as $key => $translation) {
					if(is_array($translation)) {
						$domainData[$locale][$domain][$translation['from']] = $translation['to'];
					} else {
						$domainData[$locale][$domain][$key] = $translation;
					}
				}
			}
		}

		$this->domainData = $domainData;
	}

	/**
	 * Translates a message into the defined language.
	 *
	 * @param      mixed       The message to be translated.
	 * @param      string      The domain of the message.
	 * @param      AgaviLocale The locale to which the message should be 
	 *                         translated.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function translate($message, $domain, AgaviLocale $locale = null)
	{
		if($locale && $locale !== $this->locale) {
			$oldCurrentData = $this->currentData;
			$oldLocale = $this->locale;
			$this->localeChanged($locale);
		}

		if(is_array($message)) {
			throw new AgaviException('The simple translator doesn\'t support pluralized input');
		} else {
			$data = isset($this->currentData[(string)$domain][$message]) ? $this->currentData[(string)$domain][$message] : $message;
		}

		if($locale && $locale !== $this->locale) {
			$this->currentData = $oldCurrentData;
			$this->locale = $oldLocale;
		}

		return $data;

	}

	/**
	 * This method gets called by the translation manager when the default locale
	 * has been changed.
	 *
	 * @param      AgaviLocale The new default locale.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;
		$this->currentData = AgaviToolkit::getValueByKeyList($this->domainData, AgaviLocale::getLookupPath($this->locale->getIdentifier()), array());
	}

}

?>
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
 * AgaviGettextTranslator defines a translator interface for the sample app.
 * 
 * @package    agavi
 * @subpackage translation
 * 
 * @since      0.11.0 
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 *
 * @version    $Id$
 */
class AgaviSampleAppTranslator extends AgaviBasicTranslator
{
	protected $data = array();
	protected $locale = null;

	/**
	 * Initialize this Filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context);

		$this->data = $parameters;
	}

	/**
	 * Translates a message into the defined language.
	 *
	 * @param      string The message to be translated.
	 * @param      string The domain of the message.
	 * @param      string The locale to which the message should be translated.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function translate($message, $domain, $locale)
	{
		if($locale) {
			$localeName = $locale->getName();
		} elseif($this->locale) {
			$localeName = $this->locale->getName();
		} else {
			throw new AgaviException('No default locale was set and no locale was supplied to translate');
		}

		if(isset($this->data[$localeName])) {
			if($domain) {
				$domainParts = explode('.', $domain . '.' . $message);
				return AgaviArrayPathDefinition::getValueFromArray($domainParts, $this->data[$localeName], $message);
			} elseif(isset($this->data[$localeName][$message])) {
				return $this->data[$message];
			}
		}

		return $message;
	}

	/**
	 * This method gets called by the translation manager when the default locale
	 * has been changed.
	 *
	 * @param      string The new default locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;
	}

}

?>
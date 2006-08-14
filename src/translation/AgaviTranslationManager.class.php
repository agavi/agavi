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
	 * @var        AgaviITranslator The translator implementation instance.
	 */
	protected $translator = null;

	/**
	 * @var        string The current language.
	 */
	protected $currentLanguage = null;

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

		$obj = null;
		if(isset($parameters['translator_interface'])) {
			$iface = $parameters['translator_interface'];
			if(class_exists($iface)) {
				$obj = new $iface();
			} elseif(class_exists(sprintf('Agavi%Translator', ucfirst($iface)))) {
				$class = sprintf('Agavi%Translator', ucfirst($iface));
				$obj = new $class();
			}
		} else {
			// default translator 
			$obj = new AgaviGettextTranslator();
		}

		$this->currentLanguage = isset($parameters['default_language']) ? $parameters['default_language'] : 'en_us';

		if(!($obj instanceof AgaviITranslator)) {
			throw new AgaviInitializationException('The translation interface implementation doesn\'t implement the AgaviITranslator interface');
		}

		$translatorParams = isset($parameters['translator_parameters']) ? $parameters['translator_parameters'] : array();
		$obj->initialize($context, $translatorParams);

		$this->translationInterface = $obj;
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
	 * Sets the current language.
	 *
	 * @param      string The language identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setLanguage($language)
	{
		$this->currentLangauge = $language;
	}

	/**
	 * Retrieve the current language.
	 *
	 * @return     string The current language identifier.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLanguage()
	{
		return $this->currentLangauge;
	}

	/**
	 * Translate a message into the current language.
	 *
	 * @param      string The message.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function _($message)
	{

	}
}

?>
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
 * AgaviGettextTranslator defines the translator interface for gettext.
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
class AgaviGettextTranslator extends AgaviBasicTranslator
{
	protected $domainPaths = array();

	protected $domainData = array();

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

		if(isset($parameters['text_domains']) && is_array($parameters['text_domains'])) {
			foreach($parameters['text_domains'] as $domain => $path) {
				$this->domainPaths[$domain] = $path;
			}
		}
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
			$oldDomainData = $this->domainData;
			$oldLocale = $this->locale;
			$this->localeChanged($locale);
		}

		// load domain data from file
		if(!isset($this->domainData[$domain])) {
			$this->loadDomainData($domain);
		}

		$data = isset($this->domainData[$domain][$message]) ? $this->domainData[$domain][$message] : $message;

		if($locale) {
			$this->domainData = $oldDomainData;
			$this->locale = $oldLocale;
		}

		return $data;

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
		$this->domainData = array();
	}

	public function loadDomainData($domain)
	{
		if(!isset($this->domainPaths[$domain])) {
			throw new AgaviException('Using domain "' . $domain . '" which has no path specified');
		}

		$localeName = $this->locale->getName();
		$fileNameBases = AgaviLocale::getLookupPath($localeName);

		$basePath = $this->domainPaths[$domain];

		$data = array();

		foreach($fileNameBases as $fileNameBase) {
			$fileData = AgaviGettextMoReader::readFile($basePath . '/' . $fileNameBase . '.mo');
			$data = array_merge($fileData, $data);
		}

		$this->domainData[$domain] = $data;
	}

}

?>
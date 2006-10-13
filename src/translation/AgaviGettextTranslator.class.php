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
	/**
	 * @var        array The paths to the locale files indexed by domains
	 */
	protected $domainPaths = array();

	/**
	 * @var        array The data for each domain
	 */
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
	 * @see AgaviITranslator::translate()
	 */
	public function translate($message, $domain, AgaviLocale $locale = null)
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

		if(is_array($message)) {

			$singularMsg = $message[0];
			$pluralMsg = $message[1];
			$count = $message[2];

			if(isset($this->domainData[$domain][$singularMsg])) {
				$pluralMsgs = explode(chr(0), $this->domainData[$domain][$singularMsg]);
				// TODO: parse gettext Plural-Forms header and evaluate ...
				$data = ($count != 1) ? $pluralMsgs[0] : $pluralMsgs[1];
			} else {
				$data = ($count != 1) ? $singularMsg : $pluralMsg;
			}
		} else {
			$data = isset($this->domainData[$domain][$message]) ? $this->domainData[$domain][$message] : $message;
		}

		

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

	/**
	 * Loads the data from the data file for the given domain with the current 
	 * locale.
	 *
	 * @param      string The domain to load the data for.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function loadDomainData($domain)
	{
		if(!isset($this->domainPaths[$domain])) {
			throw new AgaviException('Using domain "' . $domain . '" which has no path specified');
		}

		$localeName = $this->locale->getIdentifier();
		$fileNameBases = AgaviLocale::getLookupPath($localeName);

		$basePath = $this->domainPaths[$domain];

		$data = array();

		foreach($fileNameBases as $fileNameBase) {
			$fileName = $basePath . '/' . $fileNameBase . '.mo';
			if(is_readable($fileName)) {
				$fileData = AgaviGettextMoReader::readFile($fileName);
				$data = array_merge($fileData, $data);
			}
		}

		$this->domainData[$domain] = $data;
	}

}

?>
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
 * AgaviGettextTranslator defines the translator interface for gettext.
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
class AgaviGettextTranslator extends AgaviBasicTranslator
{
	/**
	 * @var        string A pattern for the path to the domain files.
	 */
	protected $domainPathPattern = null;

	/**
	 * @var        array The paths to the locale files indexed by domains
	 */
	protected $domainPaths = array();

	/**
	 * @var        array The data for each domain
	 */
	protected $domainData = array();
	
	/**
	 * @var        string The locale identifier of the current locale
	 */
	protected $locale = null;

	/**
	 * @var        string The name of the plural form function
	 */
	protected $pluralFormFunc = null;
	
	/**
	 * @var        bool Whether or not to write a file with all used translations
	 *                  that can be parsed using xgettext.
	 */
	protected $storeTranslationCalls = false;
	
	/**
	 * @var        string The folder to write translation call files to.
	 */
	protected $translationCallStoreDir = null;

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

		if(isset($parameters['text_domains']) && is_array($parameters['text_domains'])) {
			foreach($parameters['text_domains'] as $domain => $path) {
				$this->domainPaths[$domain] = $path;
			}
		}

		if(isset($parameters['text_domain_pattern'])) {
			$this->domainPathPattern = $parameters['text_domain_pattern'];
		}
		
		if(isset($parameters['store_calls'])) {
			$this->storeTranslationCalls = true;
			$this->translationCallStoreDir = $parameters['store_calls'];
			AgaviToolkit::mkdir($parameters['store_calls'], 0777, true);
		}
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
			if($this->pluralFormFunc) {
				$funcName = $this->pluralFormFunc;
				$msgId = (int) $funcName($count);
			} else {
				$msgId = ($count == 1) ? 0 : 1;
			}

			$msgKey = $singularMsg . chr(0) . $pluralMsg;

			if(isset($this->domainData[$domain]['msgs'][$msgKey])) {
				$pluralMsgs = explode(chr(0), $this->domainData[$domain]['msgs'][$msgKey]);
				$data = $pluralMsgs[$msgId];
			} else {
				$data = ($msgId == 0) ? $singularMsg : $pluralMsg;
			}
		} else {
			$data = isset($this->domainData[$domain]['msgs'][$message]) ? $this->domainData[$domain]['msgs'][$message] : $message;
		}

		// in "devel" mode, write a gettext() or ngettext() call to a file for xgettext parsing
		if($this->storeTranslationCalls) {
			file_put_contents(
				$this->translationCallStoreDir . DIRECTORY_SEPARATOR . $domain . '.php', 
				"" . (is_array($message) ? 
					('ngettext(' . var_export($message[0], true) . ', ' . var_export($message[1], true) . ', ' . var_export($message[2], true) . ')') :
					('gettext(' . var_export($message, true) . ')')
				) . ";\n",
			FILE_APPEND);
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;
		$this->domainData = array();
		$this->pluralFormFunc = null;
	}

	/**
	 * Loads the data from the data file for the given domain with the current 
	 * locale.
	 *
	 * @param      string The domain to load the data for.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function loadDomainData($domain)
	{
		$localeName = $this->locale->getIdentifier();
		$localeNameBases = AgaviLocale::getLookupPath($localeName);

		if(!isset($this->domainPaths[$domain])) {
			if(!$this->domainPathPattern) {
				throw new AgaviException('Using domain "' . $domain . '" which has no path specified');
			} else {
				$basePath = $this->domainPathPattern;
			}
		} else {
			$basePath = $this->domainPaths[$domain];
		}

		$basePath = AgaviToolkit::expandVariables($basePath, array('domain' => $domain));

		$data = array();
		$replaceCount = 0;
		
		foreach($localeNameBases as $localeNameBase) {
			$fileName = AgaviToolkit::expandVariables($basePath, array('locale' => $localeNameBase));
			if($fileName === $basePath) {
				// no replacing of $locale happened
				$fileName = $basePath . '/' . $localeNameBase . '.mo';
			}
			if(is_readable($fileName)) {
				$fileData = AgaviGettextMoReader::readFile($fileName);
				
				// instead of array_merge, which doesn't handle null bytes in keys properly. careful, the order matters here.
				$data = $fileData + $data;
			}
		}

		$headers = array();

		if(count($data)) {
			$headerData = str_replace("\r", '', $data['']);
			$headerLines = explode("\n", $headerData);
			foreach($headerLines as $line) {
				$values = explode(':', $line, 2);
				// skip empty / invalid lines
				if(count($values) == 2) {
					$headers[$values[0]] = $values[1];
				}
			}
		}

		$this->pluralFormFunc = null;
		if(isset($headers['Plural-Forms'])) {
			$pf = $headers['Plural-Forms'];
			if(preg_match('#nplurals=\d+;\s+plural=(.*)$#D', $pf, $match)) {
				$funcCode = $match[1];
				$validOpChars = array(' ', 'n', '!', '&', '|', '<', '>', '(', ')', '?', ':', ';', '=', '+', '*', '/', '%', '-');
				if(preg_match('#[^\d' . preg_quote(implode('', $validOpChars), '#') . ']#', $funcCode, $errorMatch)) {
					throw new AgaviException('Illegal character ' . $errorMatch[0] . ' in plural form ' . $funcCode);
				}
				
				// add parenthesis around all ternary expressions. This is done 
				// to make the ternary operator (?) have precedence over the delimiter (:)
				// This will transform 
				// "a ? 1 : b ? c ? 3 : 4 : 2" to "(a ? 1 : (b ? (c ? 3 : 4) : 2))" and
				// "a ? b ? c ? d ? 5 : 4 : 3 : 2 : 1" to "(a ? (b ? (c ? (d ? 5 : 4) : 3) : 2) : 1)"
				// "a ? b ? c ? 4 : 3 : d ? 5 : 2 : 1" to "(a ? (b ? (c ? 4 : 3) : (d ? 5 : 2)) : 1)"
				// "a ? b ? c ? 4 : 3 : d ? 5 : e ? 6 : 2 : 1" to "(a ? (b ? (c ? 4 : 3) : (d ? 5 : (e ? 6 : 2))) : 1)"
				
				$funcCode = rtrim($funcCode, ';');
				$parts = preg_split('#(\?|\:)#', $funcCode, -1, PREG_SPLIT_DELIM_CAPTURE);
				$parenthesisCount = 0;
				$unclosedParenthesisCount = 0;
				$firstParenthesis = true;
				$funcCode = '';
				for($i = 0, $c = count($parts); $i < $c; ++$i) {
					$lastPart = $i > 0 ? $parts[$i - 1] : null;
					$part = $parts[$i];
					$nextPart = $i + 1 < $c ? $parts[$i + 1] : null;
					if($nextPart == '?') {
						if($lastPart == ':') {
							// keep track of parenthesis which need to be closed 
							// directly after this ternary expression
							++$unclosedParenthesisCount;
							--$parenthesisCount;
						}
						$funcCode .= ' (' . $part;
						++$parenthesisCount;
					} elseif($lastPart == ':') {
						$funcCode .= $part . ') ';
						if($unclosedParenthesisCount > 0) {
							$funcCode .= str_repeat(')', $unclosedParenthesisCount);
							$unclosedParenthesisCount = 0;
						}
						--$parenthesisCount;
					} else {
						$funcCode .= $part;
					}
				}
				if($parenthesisCount > 0) {
					// add the missing top level parenthesis
					$funcCode .= str_repeat(')', $parenthesisCount);
				}
				$funcCode .= ';';
				$funcCode = 'return ' . str_replace('n', '$n', $funcCode);
				$this->pluralFormFunc = create_function('$n', $funcCode);
			}
		}

		$this->domainData[$domain] = array('headers' => $headers, 'msgs' => $data);
	}
}

?>
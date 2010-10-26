<?php

class AgaviSampleAppLanguageRoutingCallback extends AgaviRoutingCallback
{
	protected $availableLocales = array();
	
	public function initialize(AgaviContext $context, array &$route)
	{
		parent::initialize($context, $route);
		
		// reduce method calls
		$this->translationManager = $this->context->getTranslationManager();
		
		// store the available locales, that's faster
		$this->availableLocales = $this->context->getTranslationManager()->getAvailableLocales();
	}
	
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		// let's check if the locale is allowed
		try {
			$set = $this->context->getTranslationManager()->getLocaleIdentifier($parameters['locale']);
			// yup, worked. now lets set that as a cookie
			$this->context->getController()->getGlobalResponse()->setCookie('locale', $parameters['locale'], '+1 month');
			return true;
		} catch(AgaviException $e) {
			// uregistered or ambigious locale... uncool!
			// onNotMatched will be called for us next
			return false;
		}
	}

	public function onNotMatched(AgaviExecutionContainer $container)
	{
		// the pattern didn't match, or onMatched() returned false.
		// that's sad. let's see if there's a locale set in a cookie from an earlier visit.
		$rd = $this->context->getRequest()->getRequestData();
		
		$cookie = $rd->getCookie('locale');
		if($cookie !== null) {
			try {
				$this->translationManager->setLocale($cookie);
				return;
			} catch(AgaviException $e) {
				// bad cookie :<
				$this->context->getController()->getGlobalResponse()->unsetCookie('locale');
			}
		}
		
		if($rd->hasHeader('Accept-Language')) {
			$hasIntl = function_exists('locale_accept_from_http');
			// try to find the best match for the locale
			$locales = self::parseAcceptLanguage($rd->getHeader('Accept-Language'));
			foreach($locales as $locale) {
				try {
					if($hasIntl) {
						// we don't use this directly on Accept-Language because we might not have the preferred locale, but another one
						// in any case, it might help clean up the value a bit further
						$locale = locale_accept_from_http($locale);
					}
					$this->translationManager->setLocale($locale);
					return;
				} catch(AgaviException $e) {
				}
			}
		}
	}

	public function onGenerate(array $defaultParameters, array &$userParameters, array &$options)
	{
		if(isset($userParameters['locale'])) {
			$userParameters['locale'] = $this->getShortestLocaleIdentifier($userParameters['locale']);
		} else {
			$userParameters['locale'] = $this->getShortestLocaleIdentifier($this->translationManager->getCurrentLocaleIdentifier());
		}
		return true;
	}
	
	public function getShortestLocaleIdentifier($localeIdentifier)
	{
		static $localeMap = null;
		if($localeMap === null) {
			foreach($this->availableLocales as $locale) {
				$localeMap[$locale['identifierData']['language']][] = $locale['identifierData']['territory'];
			}
		}
		if(count($localeMap[$short = substr($localeIdentifier, 0, 2)]) > 1) {
			return $localeIdentifier;
		} else {
			return $short;
		}
	}
	
	protected static function parseAcceptLanguage($acceptLanguage)
	{
		$locales = array();
		
		if(preg_match_all('/(^|\s*,\s*)([a-zA-Z]{1,8}(-[a-zA-Z]{1,8})*)\s*(;\s*q\s*=\s*(1(\.0{0,3})?|0(\.[0-9]{0,3})))?/i', $acceptLanguage, $matches)) {
			foreach($matches[2] as &$language) {
				$language = str_replace('-', '_', $language);
			}
			foreach($matches[5] as &$quality) {
				if($quality === '') {
					$quality = '1';
				}
			}
			$locales = array_combine($matches[2], $matches[5]);
			arsort($locales, SORT_NUMERIC);
		}
		
		return array_keys($locales);
	}
}

?>
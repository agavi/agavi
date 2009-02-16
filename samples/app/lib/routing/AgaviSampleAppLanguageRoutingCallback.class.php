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
		// the pattern didn't matcb, or onMatched() returned false.
		// that's sad. let's see if there's a locale set in a cookie from an earlier visit.
		$cookie = $this->context->getRequest()->getRequestData()->getCookie('locale');
		if($cookie !== null) {
			try {
				$this->translationManager->setLocale($cookie);
			} catch(AgaviException $e) {
				// bad cookie :<
				$this->context->getController()->getGlobalResponse()->unsetCookie('locale');
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
}

?>
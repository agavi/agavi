<?php

class AgaviSampleAppLanguageRoutingCallback extends AgaviRoutingCallback
{
	protected $availableLocales = array();
	
	public function initialize(AgaviResponse $response, array &$route)
	{
		parent::initialize($response, $route);
		
		// reduce method calls
		$this->translationManager = $this->context->getTranslationManager();
		
		// store the available locales, that's faster
		$this->availableLocales = $this->context->getTranslationManager()->getAvailableLocales();
	}
	
	public function onMatched(array &$parameters)
	{
		$found = false;
		// first, let's check if the locale is allowed
		try {
			$set = $this->getContext()->getTranslationManager()->getClosestMatchingLocale($parameters['locale']);
			$found = true;
		} catch(AgaviException $e) {
			// not registered or ambigious locale... uncool!
		}
		if($found) {
			$this->response->setCookie('locale', $parameters['locale']);
		}
		return $found;
	}

	public function onNotMatched()
	{
		// no locale matched. that's sad. let's see if there's a locale set in a cookie, from an earlier visit.
		$cookie = $this->context->getRequest()->getCookie('locale');
		if($cookie !== null) {
			try {
				$this->translationManager->setLocale($cookie);
			} catch(AgaviException $e) {
			}
		}
		return;
	}

	public function onGenerate(array $defaultParameters, array &$userParameters)
	{
		$defaultParameters['locale'] = array(
			'pre' => '', 
			'val' => $this->getShortestLocaleIdentifier($this->translationManager->getCurrentLocaleIdentifier()), 
			'post' => '');
		if(isset($userParameters['locale'])) {
			$userParameters['locale'] = $this->getShortestLocaleIdentifier($userParameters['locale']);
		}
		return $defaultParameters;
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
<?php

class Default_Secure1SuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// set our template
		$this->addLayer('content', 'Secure1Success');
		$this->addLayer('decorator', 'Master');
		
		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));
		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
<?php

class Default_SearchEngineSpamSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->loadLayout();
		
		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));
		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Congratulations!', 'default.SearchEngineSpam'));
	}

	/**
	 * Execute any presentation logic for XMLRPC requests.
	 */
	public function executeXmlrpc(AgaviParameterHolder $parameters)
	{
		$this->getResponse()->setContent(array($this->getAttribute('product_price')));
	}
}

?>
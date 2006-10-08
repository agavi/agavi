<?php

class Default_SearchEngineSpamSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// set our template
		$this->setTemplate('SearchEngineSpamSuccess');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', 'Congratulations!');
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
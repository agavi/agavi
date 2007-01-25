<?php

class AgaviSampleAppDefaultBaseView extends AgaviView
{
	final public function execute(AgaviRequestDataHolder $rd)
	{
		return $this->container->createExecutionContainer(AgaviConfig::get('actions.404_module'), AgaviConfig::get('actions.404_action'));
	}

	public function executeXhtml(AgaviRequestDataHolder $rd)
	{
		return $this->executeHtml($rd);
	}

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->loadLayout();

		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));

		// also set a default title just to avoid warnings
		$this->setAttribute('title', '');
	}
}

?>
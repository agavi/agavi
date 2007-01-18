<?php

class Disabled_IndexSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// set our template
		$this->addLayer('content', 'Error404Success');
		$this->addLayer('decorator', 'Master');

		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));
		// set the title
		$this->setAttribute('_title', 'Index Action');

	}

}

?>
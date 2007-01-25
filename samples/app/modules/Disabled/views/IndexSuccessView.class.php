<?php

class Disabled_IndexSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviRequestDataHolder $rd)
	{
		$this->loadLayout();

		// set the content type
		$this->setAttribute('_contentType', $this->container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'));
		// set the title
		$this->setAttribute('_title', 'Index Action');

	}

}

?>
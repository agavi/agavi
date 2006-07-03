<?php

class Default_SearchEngineSpamSuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute ()
	{
		// forward all attributes
		$this->setAttributes($this->getContext()->getRequest()->getAttributes());

		// set our template
		$this->setTemplate('SearchEngineSpamSuccess');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', 'Congratulations!');
		
		$this->setAttribute('product_name', $this->getContext()->getRequest()->getParameter('name'));

	}

}

?>
<?php

class Default_Secure1SuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute ($parameters = array())
	{
		// forward all attributes
		$this->setAttributes($this->getContext()->getRequest()->getAttributes());

		// set our template
		$this->setTemplate('Secure1Success');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', 'Secure Action');

	}

}

?>
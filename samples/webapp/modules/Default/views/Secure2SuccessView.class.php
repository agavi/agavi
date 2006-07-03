<?php

class Default_Secure2SuccessView extends AgaviView
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
		$this->setTemplate('Secure2Success');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', 'Secure Action');

	}

}

?>
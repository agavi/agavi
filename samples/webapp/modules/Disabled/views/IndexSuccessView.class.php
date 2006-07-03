<?php

class Disabled_IndexSuccessView extends AgaviView
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
		$this->setTemplate('IndexSuccess');

		// set the title
		$this->setAttribute('_title', 'Index Action');

	}

}

?>
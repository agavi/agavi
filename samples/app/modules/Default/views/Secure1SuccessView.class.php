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
		$this->setTemplate('Secure1Success');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
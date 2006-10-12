<?php

class Default_Secure2SuccessView extends AgaviView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// set our template
		$this->setTemplate('Secure2Success');
		$this->setDecoratorTemplate('Master');

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
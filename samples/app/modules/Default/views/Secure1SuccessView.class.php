<?php

class Default_Secure1SuccessView extends AgaviSampleAppDefaultBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		parent::setupHtml($rd);

		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
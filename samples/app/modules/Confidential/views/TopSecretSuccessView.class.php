<?php

class Confidential_TopSecretSuccessView extends AgaviSampleAppConfidentialBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
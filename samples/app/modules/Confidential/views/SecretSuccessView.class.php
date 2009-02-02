<?php

class Confidential_SecretSuccessView extends AgaviSampleAppConfidentialBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->tm->_('Secure Action', 'default.Login'));

	}

}

?>
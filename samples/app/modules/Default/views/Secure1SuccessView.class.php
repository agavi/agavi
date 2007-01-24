<?php

class Default_Secure1SuccessView extends AgaviSampleAppDefaultBaseView
{

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 */
	public function executeHtml(AgaviRequestDataHolder $r)
	{
		parent::executeHtml($r);
		
		// set the title
		$this->setAttribute('title', $this->getContext()->getTranslationManager()->_('Secure Action', 'default.Login'));

	}

}

?>
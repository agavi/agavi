<?php

class Disabled_IndexSuccessView extends AgaviSampleAppDisabledBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('title', 'Index Action');
	}
}

?>
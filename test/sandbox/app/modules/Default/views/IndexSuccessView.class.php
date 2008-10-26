<?php

class Default_IndexSuccessView extends SandboxDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		$this->setAttribute('title', 'Index');
	}
}

?>
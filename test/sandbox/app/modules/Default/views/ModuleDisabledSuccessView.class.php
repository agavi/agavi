<?php

class Default_ModuleDisabledSuccessView extends SandboxDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		
		$this->getResponse()->setHttpStatusCode('503');
	}
}

?>

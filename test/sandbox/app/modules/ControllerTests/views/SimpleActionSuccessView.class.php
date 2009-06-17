<?php

class ControllerTests_SimpleActionSuccessView extends SandboxControllerTestsBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		$this->setAttribute('_title', 'SimpleAction');
	}
}

?>
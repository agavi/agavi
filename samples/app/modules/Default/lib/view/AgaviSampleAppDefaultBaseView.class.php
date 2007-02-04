<?php

class AgaviSampleAppDefaultBaseView extends AgaviView
{
	final public function execute(AgaviRequestDataHolder $rd)
	{
		return $this->createForwardContainer(AgaviConfig::get('actions.404_module'), AgaviConfig::get('actions.404_action'));
	}

	public function executeXhtml(AgaviRequestDataHolder $rd)
	{
		if(method_exists($this, 'executeHtml')) {
			return $this->executeHtml($rd);
		} else {
			return $this->execute($rd);
		}
	}

	public function setupHtml(AgaviRequestDataHolder $rd, $layout = null)
	{
		$this->loadLayout($layout);

		// also set a default title just to avoid warnings
		$this->setAttribute('title', '');
	}
}

?>
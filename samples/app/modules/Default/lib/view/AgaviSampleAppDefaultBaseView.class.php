<?php

class AgaviSampleAppDefaultBaseView extends AgaviView
{
	final public function execute(AgaviRequestDataHolder $rd)
	{
		return $this->container->createExecutionContainer(AgaviConfig::get('actions.404_module'), AgaviConfig::get('actions.404_action'));
	}

	public function executeXhtml(AgaviRequestDataHolder $rd)
	{
		if(method_exists($this, 'executeHtml')) {
			return $this->executeHtml();
		} else {
			return $this->execute();
		}
	}

	public function setupHtml(AgaviRequestDataHolder $rd)
	{
		$this->loadLayout();

		// also set a default title just to avoid warnings
		$this->setAttribute('title', '');
	}
}

?>
<?php

class Default_WelcomeSuccessView extends AgaviView
{
	public function execute(AgaviRequestDataHolder $rd)
	{
		/* Create a PHP renderer and corresponding layer for this action. This way,
		   it is guaranteed to work across output type or renderer changes. */
		$renderer = new AgaviPhpRenderer();
		$renderer->initialize($this->context, array());
		$this->appendLayer($this->createLayer('AgaviFileTemplateLayer', 'content', $renderer));
	}
}

?>
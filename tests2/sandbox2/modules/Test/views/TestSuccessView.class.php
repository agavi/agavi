<?php

class Test_TestSuccessView extends AgaviView
{
	public function execute(AgaviRequestDataHolder $parameters)
	{
		$this->appendLayer($this->createLayer('AgaviFileTemplateLayer', 'content'))->setTemplate('success');
	}
}

?>
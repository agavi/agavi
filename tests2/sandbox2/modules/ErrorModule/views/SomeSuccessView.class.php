<?php

class ErrorModule_SomeSuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('some');
	}
}

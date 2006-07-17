<?php

class Test_TestSuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('success');
	}
}

?>
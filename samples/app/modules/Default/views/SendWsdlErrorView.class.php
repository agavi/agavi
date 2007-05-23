<?php

class Default_SendWsdlErrorView extends AgaviSampleAppDefaultBaseView
{
	public function executeWsdl(AgaviRequestDataHolder $rd)
	{
		$this->getResponse()->setHttpStatusCode(404);
	}
}

?>
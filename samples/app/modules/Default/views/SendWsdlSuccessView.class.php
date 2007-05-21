<?php

class Default_SendWsdlSuccessView extends AgaviSampleAppDefaultBaseView
{
	public function executeWsdl(AgaviRequestDataHolder $rd)
	{
		return $this->getAttribute('wsdl');
	}
}

?>
<?php

class Ticket1051RoutingCallback extends AgaviRoutingCallback
{
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		$userOptions['authority'] = 'www.agavi.org';
		
		return true;
	}
}

?>
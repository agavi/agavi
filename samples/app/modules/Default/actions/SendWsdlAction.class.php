<?php

class Default_SendWsdlAction extends AgaviSampleAppDefaultBaseAction
{
	public function execute(AgaviRequestDataHolder $rd)
	{
		try {
			$sc = AgaviContext::getInstance('soap');
			$wsdl = $sc->getRouting()->getWsdlPath();
			if($wsdl && is_readable($wsdl)) {
				$this->setAttribute('wsdl', $wsdl);
				return 'Success';
			}
		} catch(AgaviException $e) {
		}
		return 'Error';
	}
}

?>
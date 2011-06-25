<?php

class Default_SendWsdlAction extends AgaviSampleAppDefaultBaseAction
{
	public function execute(AgaviRequestDataHolder $rd)
	{
		if(AgaviConfig::get('core.debug')) {
			ini_set('soap.wsdl_cache_enabled', 0);
		}
		
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
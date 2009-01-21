<?php

class Default_SearchEngineSpamAction extends AgaviSampleAppDefaultBaseAction
{
	public function executeRead(AgaviRequestDataHolder $rd)
	{
		$pfm = $this->getContext()->getModel('ProductFinder', 'Default');
		$id = $rd->getParameter('id');
		
		// was the name in the url? then validate that, too
		if($rd->hasParameter('name')) {
			$name = $rd->getParameter('name');
			$product = $pfm->retrieveByIdAndName($id, $name);
		} else {
			$product = $pfm->retrieveById($id);
		}
		if($product !== null) {
			$this->setAttribute('product', $product);
			return 'Success';
		} else {
			return 'Error';
		}
	}
}

?>
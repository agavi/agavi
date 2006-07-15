<?php

class Default_SearchEngineSpamAction extends AgaviAction
{
	public function execute($parameters = array())
	{
		$this->getContext()->getRequest()->setAttribute('product_name', $parameters['name']);
		return 'Success';
	}
}

?>
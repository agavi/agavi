<?php

class Products_Product_EditInputView extends AgaviSampleAppProductsBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		
		// set the title
		$this->setAttribute('_title', $this->tm->_('Edit Product', 'default.SearchEngineSpam'));
		
		$this->rq->setAttribute(
			'populate',
			new AgaviParameterHolder($rd->getParameter('product')->toArray()),
			'org.agavi.filter.FormPopulationFilter'
		);
	}
}

?>
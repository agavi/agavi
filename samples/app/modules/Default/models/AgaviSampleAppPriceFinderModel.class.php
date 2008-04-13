<?php

class AgaviSampleAppPriceFinderModel extends AgaviSampleAppDefaultBaseModel implements AgaviISingletonModel
{
	public function getPriceByProductName($productName)
	{
		switch(strtolower($productName)) {
			case 'brains':
				return 0.89;
			case 'chainsaws':
				return 129.99;
			case 'mad coding skills':
				return 14599;
			case 'nonsense':
				return 3.14;
			case 'viagra':
				return 14.69;
			default:
				return null;
		}
	}
}

?>
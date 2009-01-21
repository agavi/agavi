<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class Default_Error404SuccessView extends AgaviSampleAppDefaultBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->getContext()->getTranslationManager()->_('404 Not Found', 'default.ErrorActions'));

		$this->container->getResponse()->setHttpStatusCode('404');
	}

	public function executeXmlrpc(AgaviRequestDataHolder $rd)
	{
		return array(
			'faultCode' => -32601, // as per http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php
			'faultString' => 'requested method not found',
		);
	}
	
	public function executeText(AgaviRequestDataHolder $rd)
	{
		return
			'Usage: console.php <command> [OPTION]...' . PHP_EOL .
			PHP_EOL .
			'Commands:' . PHP_EOL .
			'  getprice <id>' . PHP_EOL .
			'    Finds the price of the given product ID.' . PHP_EOL .
			'    Optionally accepts a name, too, which must match the ID.' . PHP_EOL .
			'    Options:' . PHP_EOL .
			'      -name Name of the product to validate against' . PHP_EOL;
	}
}

?>
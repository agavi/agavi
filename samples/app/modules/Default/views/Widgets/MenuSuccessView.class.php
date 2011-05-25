<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class Default_Widgets_MenuSuccessView extends AgaviSampleAppDefaultBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		// will automatically load "slot" layout for us
		$this->setupHtml($rd);
		
		$items = array();
		$items[$this->ro->gen('index')] = $this->tm->_('Home', 'default.menu');
		if(!$this->us->isAuthenticated()) {
			$items[$this->ro->gen('login')] = $this->tm->_('Login', 'default.menu');
		}
		$items[$this->ro->gen('confidential.secret')] = $this->tm->_('A Secure Action', 'default.menu');
		$items[$this->ro->gen('confidential.topsecret')] = $this->tm->_('Another Secure Action', 'default.menu');
		$items[sprintf("javascript: alert('%s'); location.href = '%s';", $this->tm->_('You will now be redirected to an invalid URL. If no rewrite rules are in place, this means you will see a standard 404 page of your web server, unless you configured an ErrorDocument 404 or some similar setting. If rewrite rules are in place (i.e. no index.php part in the URL), you will be shown the Agavi 404 document. This is correct and expected behavior.', 'default.menu'), $this->ro->gen('asdjashdasd'))] = $this->tm->_('Call invalid URL', 'default.menu');
		$items[$this->ro->gen('disabled')] = $this->tm->_('Try Disabled Module', 'default.menu');
		$items[$this->ro->gen('products.index')] = $this->tm->_('Search Engine Spam', 'default.menu');
		
		$this->setAttribute('items', $items);
	}

}

?>
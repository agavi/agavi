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

class Default_IndexSuccessView extends AgaviSampleAppDefaultBaseView
{

	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);

		// set the title
		$this->setAttribute('_title', $this->tm->_('Welcome to the Agavi Sample Application', 'default.layout'));

		// Just a random parameter
		var_dump($this->ro->gen('index', array('a' => 'b')));
		// "foo=bar" is set as a default for the route
		// and will be missing from the URL although omit_defaults is off
		var_dump($this->ro->gen('index', array('foo' => 'bar')));
		// "foo=baz" is NOT set as a default for the route
		// and will still be missing from the URL
		var_dump($this->ro->gen('index', array('foo' => 'baz')));
	}

}

?>
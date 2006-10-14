<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviWebController provides web specific methods to Controller such as, url
 * redirection.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviWebController extends AgaviController
{
	/**
	 * Initialize this controller.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviResponse $response, array $parameters = array())
	{
		// initialize parent
		parent::initialize($response, $parameters);

		ini_set('arg_separator.output', AgaviConfig::get('php.arg_separator.output', '&amp;'));
	}

	/**
	 * @see        AgaviController::redirect()
	 */
	public function redirect($to)
	{
		$r = $this->getResponse();
		
		if($r->isLocked()) {
			throw new AgaviException('Response locked, cannot redirect.');
		}
		
		$r->clear();
		
		$r->setHttpHeader('Location', $to);
		
		$r->lock();
		
		if($this->redirectResponse === null) {
			$rfi = $this->context->getFactoryInfo('response');
			$this->redirectResponse = new $rfi['class']();
			$this->redirectResponse->initialize($this->context, $rfi['parameters']);
		}
		return $this->redirectResponse;
	}
}

?>
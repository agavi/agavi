
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * AgaviRequestDataHolder provides methods for retrieving client request 
 * information parameters.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRequestDataHolder extends AgaviParameterHolder
{
	/*
	 * @var        AgaviRequest The request instance.
	 */
	protected $request = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContext()
	{
		return $this->request->getContext();
	}

	/**
	 * Retrieve the request instance.
	 *
	 * @return     AgaviRequest An AgaviRequest instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Initialize this RequestDataHolder.
	 *
	 * @param      AgaviRequest An AgaviRequest instance.
	 * @param      array        An associative array of request parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviRequest $request, array $parameters = array())
	{
		$this->request = $request;
		$this->setParameters($parameters);
	}

}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviWebserviceRequest is the base class for Web Service requests
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviWebserviceRequest extends AgaviWebRequest
{
	/**
	 * @var        string The Input Data.
	 */
	protected $input = '';
	
	/**
	 * @var        string The method called by the web service request.
	 */
	protected $calledMethod = '';
	
	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// empty $_POST just to be sure
		$_POST = array();
		
		// grab the POST body
		$this->input = file_get_contents('php://input');
		
		parent::initialize($context, $parameters);
	}
	
	/**
	 * Get the name of the method called by the web service request.
	 *
	 * @return     string A method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCalledMethod()
	{
		return $this->calledMethod;
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
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
 * StdoutAppender appends a Message to stdout.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Bob Zoller (bob@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.1
 * @version   $Id$
 */
class StdoutAppender extends FileAppender
{

	/**
	 * Initialize the object.
	 * 
	 * @param array An array of parameters.
	 * 
	 * @return mixed
	 * 
	 * @author Bob Zoller (bob@agavi.org)
	 * @since 0.9.1
	 */
	public function initialize($params)
	{
		$params['file'] = 'php://stdout';
		return parent::initialize($params);
	}

}

?>

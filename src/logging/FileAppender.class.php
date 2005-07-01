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
 *
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
class FileAppender extends Appender
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	// +-----------------------------------------------------------------------+
	// | PUBLIC VARIABLES                                                      |
	// +-----------------------------------------------------------------------+

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+
	private $_handle = null;
	private $_filename = '';

	// +-----------------------------------------------------------------------+
	// | CONSTRUCTOR                                                           |
	// +-----------------------------------------------------------------------+

	public function initialize($params)
	{
		if (isset($params['file'])) {
			$this->_filename = $params['file'];
			if (!$this->_handle = fopen($this->_filename, 'a')) {
				throw new AgaviException("Cannot open file ({$this->_filename})");
			}
		}
	}

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	public function shutdown()
	{
		if ($this->_handle) {
			fclose($this->_handle);
		}
	}

	public function write(&$string)
	{
		if (fwrite($this->_handle, $string) === FALSE) {
			throw new AgaviException("Cannot write to file ({$this->_filename})");
		}
	}

}

?>

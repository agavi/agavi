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

/**
 * Interface for RequestDataHolders that allow access to Headers.
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
interface AgaviIHeadersRequestDataHolder
{
	public function hasHeader($name);
	
	public function isHeaderValueEmpty($name);
	
	public function &getHeader($name, $default = null);
	
	public function &getHeaders();
	
	public function getHeaderNames();
	
	public function setHeader($name, $value);
	
	public function setHeaders(array $headers);
	
	public function &removeHeader($name);
	
	public function clearHeaders();
	
	public function mergeHeaders(AgaviRequestDataHolder $other);
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviValidationError stores the incidents of an validation run.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviValidationArgument
{
	protected $name;
	protected $source;
	
	public function __construct($name, $source = null)
	{
		if($source === null) {
			$source = AgaviRequestDataHolder::SOURCE_PARAMETERS;
		}
		$this->name = $name;
		$this->source = $source;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getSource()
	{
		return $this->source;
	}
	
	public function getHash()
	{
		return sprintf('%s/%s', $this->source, $this->name);
	}
}

?>
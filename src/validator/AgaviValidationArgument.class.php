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
 * AgaviValidationArgument is a tupel of argument name and source that specifies 
 * the argument to validate.
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
	/**
	 * @var        string the name of the argument
	 */
	protected $name;
	
	/**
	 * @var        string the name of the source
	 */
	protected $source;
	
	/**
	 * create a new AgaviValidationArgument
	 * 
	 * @param      string the name of the argument
	 * @param      string the name of the source, if null, AgaviRequestDataHolder::SOURCE_PARAMETERS is used
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function __construct($name, $source = null)
	{
		if($source === null) {
			$source = AgaviRequestDataHolder::SOURCE_PARAMETERS;
		}
		$this->name = $name;
		$this->source = $source;
	}
	
	/**
	 * retrieve the name of the argument
	 * 
	 * @return string
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * retrieve the name of the source
	 * 
	 * @return string
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * get a unique hash value for this AgaviValidationArgument
	 * 
	 * @return string
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getHash()
	{
		return sprintf('%s/%s', $this->source, $this->name);
	}
}

?>
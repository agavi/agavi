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
 * AgaviValidationValidatorResult provides access to the validation result for a given validator.
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
class AgaviValidationValidatorResult
{
	/**
	 * @var        AgaviValidationReport the validation result instance.
	 */
	protected $validationReport;
	
	/**
	 * @var        string the affected validators name.
	 */
	protected $validatorName;
	
	/**
	 * Create a new AgaviValidationValidatorResult
	 * 
	 * @param      AgaviValidationReport the validation result instance.
	 * @param      string the affected validators name.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviValidationReport $report, $name)
	{
		$this->validationReport = $report;
		$this->validatorName = $name;
	}
	
	/**
	 * Retrieve the affected validators name.
	 * 
	 * @return     string the validators name.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getValidatorName()
	{
		return $this->validatorName;
	}
	
	/**
	 * Retrieve all AgaviValidationIncidents for this instances' validator.
	 * 
	 * @return     array a collection of affected {@see AgaviValidationIncident}s.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		$affectedIncidents = array();
		$incidents = $this->validationReport->getIncidents();
		foreach($incidents as $incident) {
			if($incident->getValidator()->getName() == $this->validatorName) {
				$affectedIncidents[] = $incident;
			}
		}
		return $affectedIncidents;
	}
}

?>
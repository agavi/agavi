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
 * AgaviValidationError stores an error message and the fields of an error.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviValidationError
{
	/**
	 * @var        string The message for this error.
	 */
	protected $message = null;

	/**
	 * @var        string The index of the message.
	 */
	protected $messageIndex = null;

	/**
	 * @var        array The fields this error affects.
	 */
	protected $fields = array();

	/**
	 * @var        AgaviValidationIncident The incident in which this error 
	 *                                     occured.
	 */
	protected $incident = null;

	/**
	 * Constructor
	 *
	 * @param      string The message of this error.
	 * @param      string The index of the message.
	 * @param      array The fields affected by this error.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($message, $messageIdx, array $fields)
	{
		$this->message = $message;
		$this->messageIndex = $messageIdx;
		$this->fields = $fields;
	}

	/**
	 * Sets the message index of this error.
	 *
	 * @param      string The message index.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setMessageIndex($messageIndex)
	{
		$this->messageIndex = $messageIndex;
	}

	/**
	 * Retrieves the message index of this error.
	 *
	 * @return     string The message index.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getMessageIndex()
	{
		return $this->messageIndex;
	}

	/**
	 * Sets the message of this error.
	 *
	 * @param      string The message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * Retrieves the message of this error.
	 *
	 * @return     string The message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Sets the incident which caused this error.
	 *
	 * @param      AgaviValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setIncident(AgaviValidationIncident $incident)
	{
		$this->incident = $incident;
	}

	/**
	 * Retrieves the incident which caused this error.
	 *
	 * @return     AgaviValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getIncident()
	{
		return $this->incident;
	}

	/**
	 * Retrieves the fields which caused this error.
	 *
	 * @return     array An array of field names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Checks if this error was caused for the given field
	 *
	 * @param      string The name of the field to check.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasField($fieldname)
	{
		return in_array($fieldname, $this->fields);
	}

}

?>

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
 * AgaviConsoleRequest provides support for console-only request information
 * such as command-line parameters.
 * 
 * @package    agavi
 * @subpackage request
 *
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviConsoleRequest extends AgaviRequest
{
	/**
	 * Constructor.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'request_data_holder_class' => 'AgaviConsoleRequestDataHolder',
		));
	}

	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$argv = self::getSourceValue('argv', array());
		// get rid of the script name
		array_shift($argv);
		
		$parameters = array();
		$input = array();
		
		$prev = '';
		foreach($argv as $arg) {
			if($arg[0] == '-') {
				// name
				$parameters[$arg] = true;
			} else {
				if($prev && $prev[0] == '-') {
					$parameters[$prev] = $arg;
				} else {
					$input[] = $arg;
				}
			}
			$prev = $arg;
		}
		
		$files = array();
		if($this->getParameter('read_stdin', false)) {
			$stdinFile = tempnam(AgaviConfig::get('core.cache_dir'), 'stdin_');
			$stdin = fopen('php://stdin', 'rb');
			stream_set_blocking($stdin, false);
			$size = stream_copy_to_stream($stdin, $handle = fopen($stdinFile, 'wb'));
			fclose($handle);
			
			$ufc = $this->getParameter('uploaded_file_class', 'AgaviUploadedFile');
			$files = array(
				$this->getParameter('stdin_file_name', 'stdin_file') => new $ufc(array(
					'name' => $stdinFile,
					'type' => 'application/octet-stream',
					'size' => $size,
					'tmp_name' => $stdinFile,
					'error' => UPLOAD_ERR_OK,
					'is_uploaded_file' => false,
				))
			);
		}

		$rdhc = $this->getParameter('request_data_holder_class');
		$this->setRequestData(new $rdhc(array(
			constant("$rdhc::SOURCE_PARAMETERS") => array(),
			constant("$rdhc::SOURCE_FILES") => $files,
		)));
		$rd = $this->getRequestData();
		
		foreach($parameters as $name => $value) {
			$rd->setParameter(substr($name, 1), $value);
		}
		
		$this->input = implode(' ', $input);
		
		$this->setMethod($this->getParameter('default_method', 'read'));
	}
	
	/**
	 * Get the command given on the command line (without parameters)
	 *
	 * @return     string The command.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function startup()
	{
		parent::startup();
		
		if($this->getParameter('unset_input', true)) {
			$_SERVER['argv'] = $_ENV['argv'] = $GLOBALS['argv'] = array();
			$_SERVER['argc'] = $_ENV['argc'] = $GLOBALS['argc'] = 0;
		}
	}
}

?>
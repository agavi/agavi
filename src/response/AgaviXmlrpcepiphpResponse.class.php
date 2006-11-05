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
 * AgaviXmlrpcepiphpResponse handles XMLRPC Web Service responses using the
 * XMLRPC-EPI extension for PHP.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id: AgaviWebResponse.class.php 1075 2006-10-01 05:14:29Z david $
 */
class AgaviXmlrpcepiphpResponse extends AgaviResponse
{
	/**
	 * @var        array The content to send back with this response.
	 */
	protected $content = array();
	
	/**
	 * Set the content for this Response.
	 *
	 * @see        AgaviResponse::setContent()
	 *
	 * @param      array The content to be sent in this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setContent(array $content)
	{
		return parent::setContent($content);
	}
	
	/**
	 * Prepend content to the existing content for this Response.
	 *
	 * @param      array The content to be prepended to this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function prependContent(array $content)
	{
		return $this->setContent($content + $this->getContent());
	}
	
	/**
	 * Append content to the existing content for this Response.
	 *
	 * @param      array The content to be appended to this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function appendContent(array $content)
	{
		return $this->setContent($this->getContent() + $content);
	}
	
	/**
	 * Clear the content for this Response
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearContent()
	{
		if(!$this->locked) {
			$this->content = array();
			return true;
		}
		return false;
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function send()
	{
		$oti = $this->context->getController()->getOutputTypeInfo();
		
		$outputOptions = array_merge(array('encoding' => 'utf-8', 'escaping' => array('markup', 'non-print')), isset($oti['parameters']['encoding']) ? array('encoding' => $oti['parameters']['encoding']) : array(), (array) $this->getParameter('output_options'));
		
		$this->content = xmlrpc_encode_request(null, $this->content, $outputOptions);
		
		header('Content-Type: text/xml; charset=' . $outputOptions['encoding']);
		header('Content-Length: ' . strlen($this->content));
		
		$this->sendContent();
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		if(!$this->locked) {
			$this->clearContent();
			$this->httpHeaders = array();
			$this->cookies = array();
		}
	}
}

?>
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
 * AgaviXmlrpcepiphpResponse handles XMLRPC Web Service responses using the
 * XMLRPC-EPI extension for PHP.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearContent()
	{
		$this->content = array();
		return true;
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function send(AgaviOutputType $outputType)
	{
		$outputOptions = array_merge(array('encoding' => $outputType->getParameter('encoding', 'utf-8'), 'escaping' => array('markup', 'non-print')), (array) $this->getParameter('output_options'));
		
		$this->content = xmlrpc_encode_request(null, $this->content, $outputOptions);
		
		header('Content-Type: text/xml; charset=' . $outputOptions['encoding']);
		header('Content-Length: ' . strlen($this->content));
		
		$this->sendContent();
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->clearContent();
		$this->httpHeaders = array();
		$this->cookies = array();
	}
}

?>
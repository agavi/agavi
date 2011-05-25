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
 * @version    $Id$
 */
class AgaviXmlrpcepiphpResponse extends AgaviResponse
{
	/**
	 * @var        array The content to send back with this response.
	 */
	protected $content = array();
	
	/**
	 * Check whether or not some content is set.
	 *
	 * @return     bool If any content is set, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.6
	 */
	public function hasContent()
	{
		return $this->content !== array();
	}
	
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
	public function setContent($content)
	{
		return parent::setContent((array) $content);
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
	public function prependContent($content)
	{
		return $this->setContent((array) $content + $this->getContent());
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
	public function appendContent($content)
	{
		return $this->setContent($this->getContent() + (array) $content);
	}
	
	/**
	 * Import response metadata (nothing in this case) from another response.
	 *
	 * @param      AgaviResponse The other response to import information from.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function merge(AgaviResponse $otherResponse)
	{
		parent::merge($otherResponse);
	}
	
	/**
	 * Redirect externally. Not implemented here.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setRedirect($to)
	{
		throw new BadMethodCallException('Redirects are not implemented for XMLRPC.');
	}
	
	/**
	 * Get info about the set redirect. Not implemented here.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for XMLRPC.');
	}

	/**
	 * Check if a redirect is set. Not implemented here.
	 *
	 * @return     bool true, if a redirect is set, otherwise false
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for XMLRPC.');
	}

	/**
	 * Clear any set redirect information. Not implemented here.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for XMLRPC.');
	}
	
	/**
	 * @see        AgaviResponse::isMutable()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isContentMutable()
	{
		return false;
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
	public function send(AgaviOutputType $outputType = null)
	{
		$encoding = array('encoding' => $this->getParameter('output_options[encoding]', 'utf-8'));
		if($outputType) {
			$encoding = array('encoding' => $outputType->getParameter('encoding', $encoding['encoding']));
		}
		
		$outputOptions = array_merge(array('escaping' => array('markup', 'non-print')), (array) $this->getParameter('output_options', array()), $encoding);
		
		$this->content = xmlrpc_encode_request(null, $this->content, $outputOptions);
		
		header('Content-Type: text/xml; charset=' . $outputOptions['encoding']);
//		header('Content-Length: ' . strlen($this->content));
		
		$this->sendContent();
	}
	
	/**
	 * Clear all response data.
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
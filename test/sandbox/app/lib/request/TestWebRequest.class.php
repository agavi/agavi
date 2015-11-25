<?php

class TestWebRequest extends AgaviWebRequest
{

	/**
	 * @param      string The protocol information.
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
	}

	/**
	 * @param      string The request URL scheme.
	 */
	public function setUrlScheme($urlScheme)
	{
		$this->urlScheme = $urlScheme;
	}

	/**
	 * @param      string The request URL hostname.
	 */
	public function setUrlHost($urlHost)
	{
		$this->urlHost = $urlHost;
	}

	/**
	 * @param      string The request URL port.
	 */
	public function setUrlPort($urlPort)
	{
		$this->urlPort = $urlPort;
	}

	/**
	 * @param      string The relative URL of the current request.
	 */
	public function setRequestUri($requestUri)
	{
		$this->requestUri = $requestUri;
	}

	/**
	 * @param      string The path part of the URL.
	 */
	public function setUrlPath($urlPath)
	{
		$this->urlPath = $urlPath;
	}

	/**
	 * @param      string The query part of the URL, or an empty string.
	 */
	public function setUrlQuery($urlQuery)
	{
		$this->urlQuery = $urlQuery;
	}

}

?>
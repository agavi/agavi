<?php

class WebRequestTest extends AgaviTestCase
{
	private $_r = null;
	private $_SERVER = array();

	public function setUp()
	{
		$this->_SERVER = $_SERVER;
		
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_PORT'] = '123';
		$_SERVER['SERVER_NAME'] = 'example.agavi.org';
		$_SERVER['REQUEST_URI'] = '/foo/bar/baz?id=4815162342';
		
		$this->_r = new AgaviWebRequest();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}
	
	public function testGetUrlScheme()
	{
		$this->assertEquals('https', $this->_r->getUrlScheme());
	}

	public function testGetUrlAuthority()
	{
		$this->assertEquals('example.agavi.org:123', $this->_r->getUrlAuthority());
	}

	public function testGetUrlPath()
	{
		$this->assertEquals('/foo/bar/baz', $this->_r->getUrlPath());
	}

	public function testGetUrlQuery()
	{
		$this->assertEquals('id=4815162342', $this->_r->getUrlQuery());
	}

	public function testGetRequestUri()
	{
		$this->assertEquals('/foo/bar/baz?id=4815162342', $this->_r->getRequestUri());
	}

	public function testGetUrl()
	{
		$this->assertEquals('https://example.agavi.org:123/foo/bar/baz?id=4815162342', $this->_r->getUrl());
	}

	public function tearDown()
	{
		$_SERVER = $this->_SERVER;
	}

}
?>
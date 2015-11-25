<?php

class TestAgaviWebResponse extends AgaviWebResponse
{
	protected function sendHttpResponseHeaders(AgaviOutputType $outputType = null)
	{
		// suppress errors when headers cannot be sent
		set_error_handler(function($errNo, $errStr) {
			return (stripos($errStr, 'headers already sent') !== false);
		}, E_WARNING);
		
		parent::sendHttpResponseHeaders($outputType);
		
		restore_error_handler();
	}
}

class AgaviWebResponseTest extends AgaviUnitTestCase
{
	
	/**
	 * @var \TestAgaviWebResponse
	 */
	private $_r = null;

	public function setUp()
	{
		$this->_r = new TestAgaviWebResponse();
		$this->_r->initialize($this->getContext());
	}

	public function testSend()
	{
		$r = $this->_r;

		$r->setContent('content');
		ob_start();
		try {
			$r->send();
		} catch(AgaviException $e) {
			// discard exception about headers already sent
		}
		$content = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('content', $content);
	}

	public function testClear()
	{
		$r = $this->_r;

		$r->setContent('content');
		$r->setCookie('cookie', 'value', 'lt1', 'p1', 'd1', false);
		$r->setHttpHeader('header', 'value');

		$this->assertEquals('content', $r->getContent());
		$r->clear();
		$this->assertEquals('', $r->getContent());
		$this->assertEquals(array(), $r->getHttpHeaders());
		$this->assertEquals(array(), $r->getCookies());
	}

	public function testSetGetContentType()
	{
		$r = $this->_r;
		$this->assertNull($r->getContentType());

		$r->setContentType('text/html');
		$this->assertEquals('text/html', $r->getContentType());

		$r->setContentType('text/xml');
		$this->assertEquals('text/xml', $r->getContentType());
	}

	public function testSetGetHttpStatusCode()
	{
		$r = $this->_r;

		$this->assertEquals('200', $r->getHttpStatusCode());
		$r->setHttpStatusCode('300');
		$this->assertEquals('300', $r->getHttpStatusCode());
		$r->setHttpStatusCode(400);
		$this->assertEquals('400', $r->getHttpStatusCode());

		try {
			$r->setHttpStatusCode('99');
			$this->fail('Expected AgaviException was not thrown!');
		} catch(AgaviException $e) {
			$this->assertEquals('400', $r->getHttpStatusCode());
		}

		try {
			$r->setHttpStatusCode('507');
			$this->fail('Expected AgaviException was not thrown!');
		} catch(AgaviException $e) {
			$this->assertEquals('400', $r->getHttpStatusCode());
		}
	}

	public function testNormalizeHttpHeaderName()
	{
		$r = $this->_r;

		$this->assertEquals('Location', $r->normalizeHttpHeaderName('lOcation'));
		$this->assertEquals('Location', $r->normalizeHttpHeaderName('Location'));
		$this->assertEquals('Html-Foo-Bar', $r->normalizeHttpHeaderName('hTML-Foo-bAr'));
		$this->assertEquals('Bar-Foo-Baz', $r->normalizeHttpHeaderName('BAR-FOO-BAZ'));

		$this->assertEquals('ETag', $r->normalizeHttpHeaderName('ETAG'));
		$this->assertEquals('ETag', $r->normalizeHttpHeaderName('etag'));
		$this->assertEquals('WWW-Authenticate', $r->normalizeHttpHeaderName('WwW-auThenticate'));
	}

	public function testSetGetHasHttpHeader()
	{
		$r = $this->_r;

		$this->assertNull($r->getHttpHeader('Location'));
		$this->assertFalse($r->hasHttpHeader('Location'));

		$r->setHttpHeader('lOCation', 'test1');
		$this->assertTrue($r->hasHttpHeader('lOCAtion'));
		$this->assertTrue($r->hasHttpHeader('Location'));

		$this->assertEquals(array('test1'), $r->getHttpHeader('Location'));

		$r->setHttpHeader('location', 'test2');
		$this->assertEquals(array('test2'), $r->getHttpHeader('location'));

		$r->setHttpHeader('Location', 'test3', false);
		$this->assertEquals(array('test2', 'test3'), $r->getHttpHeader('location'));
	}

	public function testRemoveHttpHeader()
	{
		$r = $this->_r;

		$this->assertFalse($r->hasHttpHeader('Location'));
		$this->assertNull($r->removeHttpHeader('Location'));
		$r->setHttpHeader('Location', 'test1');
		$r->setHttpHeader('Location2', 'test2');
		$this->assertTrue($r->hasHttpHeader('Location'));
		$this->assertTrue($r->hasHttpHeader('Location2'));

		$ret = $r->removeHttpHeader('lOcaTiON');
		$this->assertFalse($r->hasHttpHeader('Location'));
		$this->assertTrue($r->hasHttpHeader('Location2'));
		$this->assertEquals(array('test1'), $ret);

		$ret = $r->removeHttpHeader('Location2');
		$this->assertFalse($r->hasHttpHeader('Location'));
		$this->assertFalse($r->hasHttpHeader('Location2'));
		$this->assertEquals(array('test2'), $ret);
	}

	public function testClearHttpHeaders()
	{
		$r = $this->_r;

		$this->assertEquals(array(), $r->getHttpHeaders());

		$r->setHttpHeader('test 1', 'value 1');
		$r->setHttpHeader('test 2', 'value 2');
		$r->setHttpHeader('test 3', 'value 3');
		$r->setHttpHeader('test 4', 'value 4');
		$this->assertTrue($r->hasHttpHeader('test 1'));
		$this->assertTrue($r->hasHttpHeader('test 2'));
		$this->assertTrue($r->hasHttpHeader('test 3'));
		$this->assertTrue($r->hasHttpHeader('test 4'));

		$r->clearHttpHeaders();

		$this->assertEquals(array(), $r->getHttpHeaders());
	}

	public function testSetCookie()
	{
		$r = $this->_r;

		$info_ex = array(
			'value' => 'value',
			'lifetime' => 0,
			'path' => null,
			'domain' => '',
			'secure' => false,
			'httponly' => false,
			'encode_callback' => 'urlencode',
		);
		$r->setCookie('cookieName', 'value');
		$this->assertEquals($info_ex, $r->getCookie('cookieName'));

		$r->setCookie('cookieName', 'value 2', 300, '/foo');
		$info_ex['value'] = 'value 2';
		$info_ex['lifetime'] = 300;
		$info_ex['path'] = '/foo';
		$this->assertEquals($info_ex, $r->getCookie('cookieName'));

		$r->setCookie('cookieName2', 'value 3', 1000, '', 'foo.bar', 1);
		$info_ex = array(
			'value' => 'value 3',
			'lifetime' => 1000,
			'path' => '',
			'domain' => 'foo.bar',
			'secure' => true,
			'httponly' => false,
			'encode_callback' => 'urlencode',
		);
		$this->assertEquals($info_ex, $r->getCookie('cookieName2'));
	}
	
	/** 
	 * @runInSeparateProcess
	 */
	public function testCookieEncoding()
	{
		if(!extension_loaded('xdebug')) {
			$this->markTestSkipped('This test requires xdebug for the xdebug_get_headers() function.');
		}
		
		$r = $this->_r;
		$r->setCookie('spaceCookie',  'my value');
		$r->setCookie('plusCookie',   'my+value');
		$r->setCookie('customCookie', 'my%01value', null, null, null, null, null, false);
		$r->send();
		
		// headers_list() does sadly not work on CLI, but xdebug_get_headers() does
		// (see http://www.santiagolizardo.com/article/testing-if-http-headers-were-sent-in-php-and-phpunit)
		$headers = xdebug_get_headers();
		
		$encodedCookieValues = array();
		foreach($headers as $header) {
			list($headerName, $headerValue) = preg_split('/:\s*/', $header, 2);
			if($headerName == 'Set-Cookie') {
				$parts = preg_split('/;\s*/', $headerValue);
				list($cookieName, $cookieValue) = explode('=', $parts[0]);
				$encodedCookieValues[$cookieName] = $cookieValue;
			}
		}
		
		$this->assertEquals('my+value',   $encodedCookieValues['spaceCookie']);
		$this->assertEquals('my%2Bvalue', $encodedCookieValues['plusCookie']);
		$this->assertEquals('my%01value', $encodedCookieValues['customCookie']);
	}
	
	/** 
	 * @runInSeparateProcess
	 */
	public function testRawCookieEncoding()
	{
		if(!extension_loaded('xdebug')) {
			$this->markTestSkipped('This test requires xdebug for the xdebug_get_headers() function.');
		}
		
		$r = $this->_r;
		$r->setParameter('cookie_encode_callback', 'rawurlencode');
		$r->setCookie('spaceCookie',  'my value');
		$r->setCookie('plusCookie',   'my+value');
		$r->setCookie('customCookie', 'my%01value', null, null, null, null, null, false);
		$r->send();
		
		// headers_list() does sadly not work on CLI, but xdebug_get_headers() does
		// (see http://www.santiagolizardo.com/article/testing-if-http-headers-were-sent-in-php-and-phpunit)
		$headers = xdebug_get_headers();
		
		$encodedCookieValues = array();
		foreach($headers as $header) {
			list($headerName, $headerValue) = preg_split('/:\s*/', $header, 2);
			if($headerName == 'Set-Cookie') {
				$parts = preg_split('/;\s*/', $headerValue);
				list($cookieName, $cookieValue) = explode('=', $parts[0]);
				$encodedCookieValues[$cookieName] = $cookieValue;
			}
		}
		
		$this->assertEquals('my%20value', $encodedCookieValues['spaceCookie']);
		$this->assertEquals('my%2Bvalue', $encodedCookieValues['plusCookie']);
		$this->assertEquals('my%01value', $encodedCookieValues['customCookie']);
	}
}

?>
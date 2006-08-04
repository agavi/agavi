<?php

class WebResponseTest extends AgaviTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new NoHeadersAgaviWebResponse();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testSend()
	{
		$r = $this->_r;

		$r->setContent('content');
		$this->assertTrue($r->isDirty());
		ob_start();
		try {
			$r->send();
		} catch(AgaviException $e) {
			// discard exception about headers already sent
		}
		$content = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('content', $content);
		$this->assertFalse($r->isDirty());
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
		$info = $r->exportInfo();
		$this->assertEquals(array(), $info['cookies']);
	}

	public function testImportExportInfo()
	{
		$r = $this->_r;

		$r->setContent('test1');
		$this->assertSame(array('content' => 'test1', 'locked' => false, 'httpStatusCode' => '200', 'httpHeaders' => array(), 'cookies' => array()), $r->export());

		$r->import(array('content' => 'test2'));
		$this->assertSame(array('content' => 'test2', 'locked' => false, 'httpStatusCode' => '200', 'httpHeaders' => array(), 'cookies' => array()), $r->export());


		$array_ex = array('content' => 'test3', 'locked' => false, 'httpStatusCode' => '300', 'httpHeaders' => array('Location' => '/foo.html'), 'cookies' => array('Cookie' => 'value'));
		$r->import($array_ex);
		$this->assertSame($array_ex, $r->export());

		$array_ex = array('content' => 'test3', 'locked' => true, 'httpStatusCode' => '300', 'httpHeaders' => array('Location' => '/bar.html'), 'cookies' => array('Cookie 2' => 'value 2'));
		$r->import($array_ex);
		$this->assertSame($array_ex, $r->export());

		unset($array_ex['content']);
		$this->assertSame($array_ex, $r->exportInfo());
	}

	public function testMerge()
	{
		$r = $this->_r;

		$r->setCookie('cookie 1', 'value 1', 'lt1', 'p1', 'd1', false);
		$r->setCookie('cookie 2', 'value 2', 'lt2', 'p2', 'd2', false);
		$this->assertTrue($r->merge(array('cookies' => array('cookie 2' => array('value' => 'value 3')) , 'httpStatusCode' => '300', 'httpHeaders' => 'new Header')));
		$info_ex = array(
			'locked' => false,
			'httpStatusCode' => '200',
			'httpHeaders' => array(),
			'cookies' => array(
				'cookie 2' => array(
					'value' => 'value 2',
					'lifetime' => 'lt2',
					'path' => 'p2',
					'domain' => 'd2',
					'secure' => false,
				),
				'cookie 1' => array(
					'value' => 'value 1',
					'lifetime' => 'lt1',
					'path' => 'p1',
					'domain' => 'd1',
					'secure' => false,
				),
			),
		);
		$info = $r->exportInfo();
		$this->assertSame($info_ex, $info);

		$r->lock();
		$info_ex['locked'] = true;
		$this->assertFalse($r->merge(array('cookies' => array('cookie 2' => array('value' => 'value 4')))));
		$this->assertTrue($r->isLocked());
		$info = $r->exportInfo();
		$this->assertEquals($info_ex, $info);
	}

	public function testAppend()
	{
		$r = $this->_r;

		$r->setCookie('cookie 1', 'value 1', 'lt1', 'p1', 'd1', false);
		$r->setCookie('cookie 2', 'value 2', 'lt2', 'p2', 'd2', false);
		$this->assertTrue($r->append(array('cookies' => array('cookie 2' => array('value' => 'value 3'), 'cookie 3' => array('value' => 'value 4')) , 'httpStatusCode' => '300', 'httpHeaders' => array('Location' => '/bar.html'))));
		$info_ex = array(
			'locked' => false,
			'httpStatusCode' => '300',
			'httpHeaders' => array('Location' => '/bar.html'),
			'cookies' => array(
				'cookie 1' => array(
					'value' => 'value 1',
					'lifetime' => 'lt1',
					'path' => 'p1',
					'domain' => 'd1',
					'secure' => false,
				),
				'cookie 2' => array(
					'value' => 'value 3',
				),
				'cookie 3' => array(
					'value' => 'value 4',
				),
			),
		);
		$info = $r->exportInfo();
		$this->assertSame($info_ex, $info);

		$r->lock();
		$info_ex['locked'] = true;
		$this->assertFalse($r->merge(array('cookies' => array('cookie 2' => array('value' => 'value 4')))));
		$this->assertTrue($r->isLocked());
		$info = $r->exportInfo();
		$this->assertEquals($info_ex, $info);
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

		$info = $r->exportInfo();
		$this->assertEquals(array(), $info['httpHeaders']);

		$r->setHttpHeader('test 1', 'value 1');
		$r->setHttpHeader('test 2', 'value 2');
		$r->setHttpHeader('test 3', 'value 3');
		$r->setHttpHeader('test 4', 'value 4');
		$this->assertTrue($r->hasHttpHeader('test 1'));
		$this->assertTrue($r->hasHttpHeader('test 2'));
		$this->assertTrue($r->hasHttpHeader('test 3'));
		$this->assertTrue($r->hasHttpHeader('test 4'));

		$r->clearHttpHeaders();

		$info = $r->exportInfo();
		$this->assertEquals(array(), $info['httpHeaders']);
	}

	public function testSetCookie()
	{
		$r = $this->_r;

		$info_ex = array(
			'value' => 'value',
			'lifetime' => 0,
			'path' => '/',
			'domain' => '',
			'secure' => 0,
		);
		$r->setCookie('cookieName', 'value');
		$info = $r->exportInfo();
		$this->assertEquals($info_ex, $info['cookies']['cookieName']);

		$r->setCookie('cookieName', 'value 2', 300, '/foo');
		$info_ex['value'] = 'value 2';
		$info_ex['lifetime'] = 300;
		$info_ex['path'] = '/foo';
		$info = $r->exportInfo();
		$this->assertEquals($info_ex, $info['cookies']['cookieName']);

		$r->setCookie('cookieName2', 'value 3', 1000, '', 'foo.bar', 1);
		$info_ex = array(
			'value' => 'value 3',
			'lifetime' => 1000,
			'path' => '',
			'domain' => 'foo.bar',
			'secure' => 1,
		);
		$info = $r->exportInfo();
		$this->assertEquals($info_ex, $info['cookies']['cookieName2']);
	}
}

?>
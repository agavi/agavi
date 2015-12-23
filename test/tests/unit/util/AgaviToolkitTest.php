<?php
if(!class_exists('AgaviToolkit')) {
	include(__DIR__ . '/../../../../src/util/AgaviToolkit.class.php');
}

if(!class_exists('AgaviConfig')) {
	include(__DIR__ . '/../../../../src/config/AgaviConfig.class.php');
}

if(!class_exists('AgaviException')) {
	include(__DIR__ . '/../../../../src/exception/AgaviException.class.php');
}

class AgaviToolkitTest extends AgaviPhpUnitTestCase
{

	public function __construct($name = NULL, $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// $this->setRunTestInSeparateProcess(true);
	}

	public function testNormalizePath()
	{
		$this->assertEquals('path', AgaviToolkit::normalizePath("path"));
		$this->assertEquals('/path/warm/hot/unbearable', AgaviToolkit::normalizePath('/path/warm/hot/unbearable'));
		$this->assertEquals('/path/warm/hot/unbearable', AgaviToolkit::normalizePath('\path\warm\hot\unbearable'));
		$this->assertEquals('/path/warm/hot//unbearable', AgaviToolkit::normalizePath('\path\\warm\hot\\\\unbearable'));
	}

	public function testMkdir()
	{
		$this->assertTrue(AgaviToolkit::mkdir('_testing_path'));
		rmdir('_testing_path');
	}

	public function testStringBase()
	{
		$amount = 0;
		$this->assertEquals("string", AgaviToolkit::stringBase("stringbase", "stringother"));
		$this->assertEquals("string", AgaviToolkit::stringBase("stringbase", "stringother", $amount));
		$this->assertEquals(6, $amount);
		$this->assertEquals("hu", AgaviToolkit::stringBase("hurray", "hungry"));
		$this->assertEquals(NULL, AgaviToolkit::stringBase("astringbase", "stringother"));
	}

	public function testExpandVariables()
	{
		$string = "{bbq}";
		$arguments = array('hehe' => 'hihi', '{bbq}' => 'soon');
		$this->assertEquals('{bbq}', AgaviToolkit::expandVariables($string));
		$this->assertEquals('${foo}', AgaviToolkit::expandVariables('$foo'));
		$this->assertEquals('${foo}', AgaviToolkit::expandVariables('{$foo}'));
	}

	public function testExpandDirectives()
	{
		AgaviConfig::set('whatever', 'something');
		$value = "whatever %directive% asdasdasd %whatever% ";
		$result = "whatever %directive% asdasdasd something ";
		$this->assertEquals($result, AgaviToolkit::expandDirectives($value));
	}

	public function testFloorDivide()
	{
		$rem = 0;
		$this->assertEquals(3, AgaviToolkit::floorDivide(10, 3, $rem));
		$this->assertEquals(1, $rem);
		$this->assertEquals(0, AgaviToolkit::floorDivide(0, 2, $rem));
		$this->assertEquals(0, $rem);
		$this->assertEquals(3, AgaviToolkit::floorDivide(10.5, 3, $rem));
		$this->assertEquals(1, $rem);
	}


	/**
	 * @expectedException
	 */
	public function testFloorDivideException()
	{
		$this->setExpectedException('AgaviException');
		AgaviToolkit::floorDivide(6.9, 3.4, $rem);
	}

	 /**
	 * @expectedException PHPUnit_Framework_Error
	 */
	public function testFloorDivideByZero()
	{
		AgaviToolkit::floorDivide(10, 0, $rem);
	}

	public function testIsPortNecessary()
	{
		$this->assertTrue(AgaviToolkit::isPortNecessary('some scheme', 8800));
		$this->assertFalse(AgaviToolkit::isPortNecessary('ftp', 21));
		$this->assertFalse(AgaviToolkit::isPortNecessary('ssh', 22));
		$this->assertFalse(AgaviToolkit::isPortNecessary('https', 443));
		$this->assertFalse(AgaviToolkit::isPortNecessary('nttp', 119));
	}

	public function testGetValueByKeyList()
	{
		$array = array('one' => 'edno', 'two' => 'dve', 'three' => 'tri', 'four' => 'chetiri');
		$keys = array('one', 'two', 'three');
		$this->assertEquals('edno', AgaviToolkit::getValueByKeyList($array, $keys));
		$this->assertEquals('dve', AgaviToolkit::getValueByKeyList($array, array('two')));
		$this->assertEquals('dve', AgaviToolkit::getValueByKeyList($array, array('two'), 'default'));
		$this->assertEquals(NULL, AgaviToolkit::getValueByKeyList($array, array('five')));
		$this->assertEquals('pet', AgaviToolkit::getValueByKeyList($array, array('five'), 'pet'));
	}

	public function testIsNotArray()
	{
		$value1 = array('baz' => 'boo');
		$value2 = array('baz', 'boo');
		$this->assertTrue(AgaviToolkit::isNotArray("path"));
		$this->assertFalse(AgaviToolkit::isNotArray($value1));
		$this->assertFalse(AgaviToolkit::isNotArray($value2));
	}

	public function testUniqid()
	{
		$id1 = AgaviToolkit::uniqid();
		$id2 = AgaviToolkit::uniqid();
		$id3 = AgaviToolkit::uniqid();
		$this->assertNotEquals($id1, $id2);
		$this->assertNotEquals($id3, $id2);
		$this->assertNotEquals($id1, $id3);
	}

	public function testUniqidWithPrefix()
	{
		$id1 = AgaviToolkit::uniqid('001');
		$id2 = AgaviToolkit::uniqid('001');
		$this->assertNotEquals($id1, $id2);
		$this->assertContains('001', $id1);
	}

	public function testCanonicalName()
	{
		$this->assertEquals('path', AgaviToolkit::canonicalName("path"));
		$this->assertEquals('/path/warm/hot/unbearable', AgaviToolkit::canonicalName("/path/warm/hot/unbearable"));
		$this->assertEquals('path/warm/hot/unbearable', AgaviToolkit::canonicalName("path.warm.hot.unbearable"));
		$this->assertEquals('/path//warm/hot///unbearable', AgaviToolkit::canonicalName(".path..warm.hot...unbearable"));
	}

	public function testEvaluateModuleDirective()
	{
		AgaviConfig::set('replace.me', 'replaced value $foo $bar $baz');
		AgaviConfig::set('modules.foo.bar', 'value $foo %replace.me% %nonexistant%');
		$array = array('foo' => 'replaced_foo', 'bar' => 'replaced_bar');
		$retval = 'value replaced_foo replaced value replaced_foo replaced_bar ${baz} %nonexistant%';
		$actual = AgaviToolkit::evaluateModuleDirective('foo', 'bar', $array);
		$this->assertEquals($retval, $actual);
	}
	
	/**
	 * @dataProvider literalizeData
	 */
	public function testLiteralize($rawValue, $expectedResult, $settings)
	{
		foreach($settings as $key => $value) {
			AgaviConfig::set($key, $value);
		}
		
		$literalized = AgaviToolkit::literalize($rawValue);
		
		$this->assertEquals($expectedResult, $literalized);
	}
	
	public function literalizeData()
	{
		return array(
			'null' => array(null, null, array()),
			'empty string' => array('', null, array()),
			'array("foo" => "bar")' => array(array('foo' => 'bar'), array('foo' => 'bar'), array()),
			'(string)true' => array('true', true, array()),
			'(string)false' => array('false', false, array()),
			'(string)yes' => array('yes', true, array()),
			'(string)no' => array('no', false, array()),
			'(string)on' => array('on', true, array()),
			'(string)off' => array('off', false, array()),
			'(string)single space' => array(' ', null, array()),
			'(string)multiple spaces' => array('    ', null, array()),
			'(string)newline' => array("\n", null, array()),
			'(string)newline and space' => array(" \n ", null, array()),
			'(string)space true space' => array(' true ', true, array()),
			'(string)%test.replace%' => array('%test.replace%', 'fooo', array('test.replace' => 'fooo')),
			'(int)5' => array(5, 5, array())
		);
	}
	
	/**
	 * @dataProvider pathData
	 */
	public function testIsPathAbsolute($path, $expected)
	{
		if($expected) {
			$this->assertTrue(AgaviToolkit::isPathAbsolute($path));
		} else {
			$this->assertFalse(AgaviToolkit::isPathAbsolute($path));
		}
	}
	
	public function pathData()
	{
		$data = array(
			'c:/' => array('c:/', true),
			'c:\\' => array('c:\\', true),
			'c:/Windows' => array('c:/Windows', true),
			'g:/Windows/bar' => array('g:/Windows/bar', true),
			'c:\\windows\\foo' => array('c:\\windows\\foo', true),
			':/foo' => array(':/foo', false),
			// UNC paths are absolute too
			'(unc)\\\\some.host' => array('\\\\some.host', true),
			'(unc)\\\\some.host\\foo' => array('\\\\some.host\\foo', true),
			'(unc)\\some.host\\foo' => array('\\some.host\\foo', false),
			
			'/' => array('/', true),
			'/root' => array('/root', true),
			'/FoO/bAR' => array('/FoO/bAR', true),
			'./FoO/bAR' => array('./FoO/bAR', false),
			'../FoO/bAR' => array('../FoO/bAR', false),
			
			// (php does not support backslashes on *nix)
			'\\foo' => array('\\foo', false),
			'\\foo\\bar' => array('\\foo\\bar', false),
			
			'c:' => array('c:', false),
			's/foo/bar' => array('s/foo/bar', false),
			'c:foo' => array('c:foo', false)
		);
		foreach($data as $key => $value) {
			$data['file://' . $key] = array('file://' . $value[0], $value[1]);
		}
		return $data;
	}
	
	/**
	 * @dataProvider urlData
	 */
	public function testBuildUrl($parts, $url)
	{
		$this->assertEquals($url, AgaviToolkit::buildUrl($parts));
	}
	
	public function urlData()
	{
		return array(
			array(
				array('host' => 'example.com'),
				'//example.com/',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com'),
				'http://example.com/',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'port' => '80'),
				'http://example.com:80/',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'user' => 'user', 'pass' => 'pass'),
				'http://user:pass@example.com/',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'path' => '/path'),
				'http://example.com/path',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'query' => 'param1=foo&param2=bar'),
				'http://example.com/?param1=foo&param2=bar',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'fragment' => 'fragment'),
				'http://example.com/#fragment',
			),
			array(
				array('scheme' => 'http', 'host' => 'example.com', 'port' => '80', 'user' => 'user', 'pass' => 'pass', 'path' => '/path', 'query' => 'param1=foo&param2=bar', 'fragment' => 'fragment'),
				'http://user:pass@example.com:80/path?param1=foo&param2=bar#fragment',
			),
			array(
				parse_url('//example.com/'),
				'//example.com/',
			),
			array(
				parse_url('http://example.com/?'),
				'http://example.com/',
			),
			array(
				parse_url('http://example.com/#'),
				'http://example.com/',
			),
			array(
				parse_url('http://user:pass@example.com:80/path?param1=foo&param2=bar#fragment'),
				'http://user:pass@example.com:80/path?param1=foo&param2=bar#fragment',
			),
		);
	}
	
}

?>

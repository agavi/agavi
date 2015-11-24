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
	
	public function testVksprintfNormalArguments()
	{
		$tests = array(
			array(
				"input" => 'Nothing to do here!',
				"arguments" => array(
					'to do' => 'nothing'
				),
				"expected" => 'Nothing to do here!'
			),
			array(
				"input" => '%s %s',
				"arguments" => array(
					"blah" => "blub",
					"foo" => "bar"
				),
				"expected" => 'blub bar'
			),
			array(
				"input" => '%04d %s',
				"arguments" => array(
					"blah" => 123,
					"foo" => "bar"
				),
				"expected" => '0123 bar'
			),
			array(
				"input" => 'The %2$s contains %1$05d monkeys',
				"arguments" => array(
					'one' => 6,
					'two' => 'tree'
				),
				"expected" => 'The tree contains 00006 monkeys'
			),
			array(
				"input" => 'The %2$s contains %1$04d monkeys',
				"arguments" => array(
					5,
					'tree'
				),
				"expected" => 'The tree contains 0005 monkeys'
			),
			array(
				"input" => 'The %2$s contains %1$01d monkeys',
				"arguments" => array(
					5 => 7,
					3 => 'house'
				),
				"expected" => 'The house contains 7 monkeys'
			),
			array(
				"input" => ">>>%'#-10s<<<",
				"arguments" => array(
					"foo bar"
				),
				"expected" => '>>>foo bar###<<<'
			),
			array(
				"input" => ">>>%'#-10.10s<<<",
				"arguments" => array(
					"foo bar is TOOLONG"
				),
				"expected" => '>>>foo bar is<<<'
			),
			array(
				"input" => '%1$s %1$\'#10s %1$s!',
				"arguments" => array(
					'badger'
				),
				"expected" => 'badger ####badger badger!'
			),
			array(
				"input" => '%1$s %1$\'#10s %1$s!',
				"arguments" => array(
					'badger',
					'foo' => 'bar'
				),
				"expected" => 'badger ####badger badger!'
			),
			array(
			    "input" => '',
			    "arguments" => array(
			    ),
			    "expected" => ''
			),
		);
		
		foreach($tests as $test) {
			$this->assertEquals($test["expected"], AgaviToolkit::vksprintf($test["input"], $test["arguments"]));
		}
	}
	
	public function testVksprintfNamedArguments()
	{
		$tests = array(
			array(
				"input" => 'Some %foo$s parameter',
				"arguments" => array(
					"foo" => "named"
				),
				"expected" => 'Some named parameter'
			),
			array(
				"input" => 'Some %formatted$\'#-10s and positional %1$s and normal %s parameter',
				"arguments" => array(
					"formatted" => "value"
				),
				"expected" => 'Some value##### and positional value and normal value parameter'
			),
			array(
				"input" => '%param$s must be between %min$03d and %max$03d.',
				"arguments" => array(
					'min' => 3,
					'max' => 99,
					'param' => 'Value'
				),
				"expected" => 'Value must be between 003 and 099.'
			),
			array(
				"input" => '%hyphened-param$s',
				"arguments" => array(
					'hyphened-param' => 'Value'
				),
				"expected" => 'Value'
			),
			array(
				"input" => '%base[param]$s',
				"arguments" => array(
					'base[param]' => 'Value'
				),
				"expected" => 'Value'
			),
			array(
				"input" => '%most.complex-base[param][eters]$s',
				"arguments" => array(
					'most.complex-base[param][eters]' => 'Value'
				),
				"expected" => 'Value'
			),
		);
		
		foreach($tests as $test) {
			$this->assertEquals($test["expected"], AgaviToolkit::vksprintf($test["input"], $test["arguments"]));
		}
	}
	
	public function testVksprintfInvalidInput()
	{
		$this->setExpectedException('RuntimeException');
		AgaviToolkit::vksprintf(array('asdf', 'qwer'), array());
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
	
}

?>

<?php
if(!class_exists('AgaviToolkit')) {
	include(dirname(__FILE__) . '/../../../../src/util/AgaviToolkit.class.php');
}

if(!class_exists('AgaviConfig')) {
	include(dirname(__FILE__) . '/../../../../src/config/AgaviConfig.class.php');
}

if(!class_exists('AgaviException')) {
	include(dirname(__FILE__) . '/../../../../src/exception/AgaviException.class.php');
}

class AgaviToolkitTest extends PHPUnit_Framework_TestCase
{

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// $this->setRunTestInSeparateProcess(true);
	}

	public function testIsPathAbsolute()
	{
		$this->assertTrue(AgaviToolkit::isPathAbsolute("/path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("\path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("\\path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("\\\\\\\path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("h:\path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("h:/path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("h://path"));
		$this->assertTrue(AgaviToolkit::isPathAbsolute("h:/"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h:"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h:path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h/path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h:path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h\path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute(":/path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("hh:path"));
		$this->assertFalse(AgaviToolkit::isPathAbsolute("h:p"));
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
		$this->assertTrue(AgaviToolkit::mkdir('path'));
		rmdir('path');
		$this->assertTrue(AgaviToolkit::mkdir('/newpath'));
		rmdir('/newpath');
		$this->assertTrue(AgaviToolkit::mkdir('anotherpath'));
		rmdir('anotherpath');
		$this->assertFalse(AgaviToolkit::mkdir('contextpath', 0777, false, "path"));
		$this->assertFalse(AgaviToolkit::mkdir('ehh', 0777, false, "lala"));
		$this->assertFalse(AgaviToolkit::mkdir('az', 0666, true, "ti"));
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

	public function testLiteralize()
	{
		$this->assertEquals(NULL, AgaviToolkit::literalize(NULL));
		$this->assertEquals(NULL, AgaviToolkit::literalize(""));
		$value = array('baz' => 'boo');
		$this->assertEquals($value, AgaviToolkit::literalize($value));
		$this->assertEquals(2, AgaviToolkit::literalize(2));
		$this->assertEquals(true, AgaviToolkit::literalize(true));
		$this->assertEquals(true, AgaviToolkit::literalize('On'));
		$this->assertEquals(true, AgaviToolkit::literalize('YES'));
		$this->assertEquals(false, AgaviToolkit::literalize(false));
		$this->assertEquals(false, AgaviToolkit::literalize('no'));
		$this->assertEquals(false, AgaviToolkit::literalize('oFf'));
		$this->assertEquals("lalala", AgaviToolkit::literalize("lalala"));
		$this->assertEquals("lAlAla", AgaviToolkit::literalize("lAlAla"));
		$this->assertEquals("l Al Ala", AgaviToolkit::literalize(" l Al Ala "));
		$this->assertEquals("2", AgaviToolkit::literalize("2"));
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

}

?>

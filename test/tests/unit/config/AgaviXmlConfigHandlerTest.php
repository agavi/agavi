<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class AgaviXmlConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testParseXincludeSimple()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/xinclude_simple.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'Name' => 'A',
		);
		$this->assertSame($expected, $actual);
	}


	public function testParseXincludeGlobSimple()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/xinclude_glob_simple.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'Name' => 'C',
		);
		$this->assertSame($expected, $actual);
	}


	public function testParseXincludeGlobBrace()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/xinclude_glob_brace.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'Name' => 'B',
		);
		$this->assertSame($expected, $actual);
	}


	public function testParseXincludeEncoding()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/xinclude_encoding.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'Name' => 'C',
		);
		$this->assertSame($expected, $actual);
	}
}
?>
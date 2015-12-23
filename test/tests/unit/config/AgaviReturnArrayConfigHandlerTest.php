<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class AgaviReturnArrayConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testParseMixed()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/rach_mixed.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X', 'value' => ''),
			'section3' => array('One' => '1', 'Three' => '3', 'Two' => '2')
		);
		$this->assertSame($expected, $actual);
	}


	public function testParseAttributes()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/rach_attributes.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C', 'value' => ''), 
			'section2' => array('Three' => AgaviConfig::get('core.config_dir'), 'Two' => false, 'One' => true, 'value' => ''),
		);
		$this->assertSame($expected, $actual);
	}


	public function testParseTags()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/rach_tags.xml');
		$actual = $this->includeCode($RACH->execute($document));
		$expected = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'),
		);
		$this->assertSame($expected, $actual);
	}

	public function testParseComplex()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$document = $this->parseConfiguration(AgaviConfig::get('core.config_dir') . '/tests/rach_complex.xml');
		$actual = $this->includeCode($RACH->execute($document));

		$expected = array(
			'cachings' => array(
				'Browse' => array(
					'enabled' => true,
					'action' => AgaviConfig::get('core.app_dir'),
					'groups' => array(
						'foo' => 'bar',
						'categories' => '',
						'id' => array(
							'source' => 'request.parameter',
							'value' => '',
						),
						'LANG' => array(
							'source' => 'constant',
							'value' => '',
						),
						'admin' => array(
							'source' => 'user.credential',
							'value' => '',
						),
					),
					'decorator' => array(
						'include' => false,
						'slots' => array(
							'breadcrumb',
						),
						'variables' => array(
							'bar' => 'baz',
							'_title',
							'_section',
						),
					),
					'variables' => array(
						'categoryId' => array(
							'source' => 'request.attribute',
							'value' => '',
						),
						'isRootCat' => array(
							'source' => 'request.attribute',
							'value' => '',
						),
					),
				),
			),
		);
		$this->assertEquals($expected, $actual);
	}
}
?>
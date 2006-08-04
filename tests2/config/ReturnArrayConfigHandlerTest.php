<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class ReturnArrayConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testParseMixed()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $this->includeCode($RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_mixed.xml'));
		$ex_simple = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'),
			'section3' => array('Two' => '2', 'One' => '1', 'Three' => '3')
		);
		$this->assertEquals($ex_simple, $simple);
	}


	public function testParseAttributes()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $this->includeCode($RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_attributes.xml'));
		$ex_simple = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => AgaviConfig::get('core.config_dir'), 'Two' => false, 'One' => true),
		);
		$this->assertEquals($ex_simple, $simple);
	}


	public function testParseTags()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $this->includeCode($RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_tags.xml'));
		$ex_simple = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'),
		);
		$this->assertEquals($ex_simple, $simple);
	}

	public function testParseComplex()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $this->includeCode($RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_complex.xml'));

		$ex_simple = array(
			'cachings' => array(
				'Browse' => array(
					'action' => '%core.app_dir%',
					'groups' => array(
						'categories' => array('name' => 'categories'),
						'id' => array('name' => 'id', 'source' => 'request.parameter'),
						'LANG' => array('name' => 'LANG', 'source' => 'constant'),
						'admin' => array('name' => 'admin', 'source' => 'user.credential'),
						'foo' => 'bar',
					),
					'decorator' => array(
						'slots' => array('breadcrumb'),
						'variables' => array('_title', '_section', 'bar' => 'baz'),
						'include' => false,
					),
					'variables' => array(
						'categoryId' => array('name' => 'categoryId', 'source' => 'request.attribute'),
						'isRootCat' => array('name' => 'isRootCat', 'source' => 'request.attribute'),
					),
					'name' => 'Browse',
					'enabled' => true,
				),
			),
		);
		$this->assertEquals(var_export($ex_simple,1), var_export($simple,1));
	}
}
?>
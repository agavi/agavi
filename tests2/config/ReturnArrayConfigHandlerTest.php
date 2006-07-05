<?php

class ReturnArrayConfigHandlerTest extends AgaviTestCase
{
	public function testParseMixed()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_mixed.xml');
		$simple_array = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'),
			'section3' => array('Two' => '2', 'One' => '1', 'Three' => '3')
		);
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertEquals($ex_simple, $simple);
	}


	public function testParseAttributes()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_attributes.xml');
		$simple_array = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => AgaviConfig::get('core.config_dir'), 'Two' => false, 'One' => true),
		);
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertEquals($ex_simple, $simple);
	}


	public function testParseTags()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_tags.xml');
		$simple_array = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'),
		);
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertEquals($ex_simple, $simple);
	}

	public function testParseComplex()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$simple = $RACH->execute(AgaviConfig::get('core.config_dir') . '/tests/rach_complex.xml');

		$simple_array = array(
			'cachings' => array(
				'Browse' => array(
					'action' => '%core.webapp_dir%',
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
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertEquals($ex_simple, $simple);
	}
}
?>
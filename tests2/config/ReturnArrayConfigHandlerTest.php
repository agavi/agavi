<?php

class ReturnArrayConfigHandlerTest extends AgaviTestCase
{
	public function testParseSimpleIniFileIntoArray()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$this->assertType('AgaviReturnArrayConfigHandler', $RACH);
		$simple = $RACH->execute(AgaviConfig::get('core.config_dir') . '/RACHsimple.ini');
		$simple_array = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'));
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertEquals($simple, $ex_simple);
	}

	public function testParseDottedIniFileIntoNestedArray()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$this->assertType('AgaviReturnArrayConfigHandler', $RACH);
		
		$dotted = $RACH->execute(AgaviConfig::get('core.config_dir') . '/RACHwithDots.ini');
		$dotted_array = array(
			'one' => array(
				'type' => 'associative',
				'sub' => array(
					'a' => 'apple',
					'b' => 'bubble',
					'c' => 'candy'
				)
			),
			'two' => array(
				'type' => 'numeric',
				'sub' => array('dot', 'dot dot', 'dot dot dot')
			),
			'three' => array(
				'type' => 'withdots',
				'three' => array(
					'sub' => array(
						'a' => 'A',
						'b' => 'B'
					)
				)
			),
			'four' => array(
				'type' => 'moredots',
				'four' => array(
					'sub' => array('dot', 'dot dot')
				)
			),
			'five' => array(
				'type' => 'numeric',
				'sub' => array(
					0 => array(
						'foo' => 'first foo',
						'bar' => 'first bar'
					),
					1 => array(
						'foo' => 'second foo',
						'bar' => 'second bar'
					)
				)
			)
		);
		$ex_dotted = '<?php return '. var_export($dotted_array, true) .';?>';
		$this->assertSame($dotted, $ex_dotted);
	}

	public function testBooleanValuesParsedCorrectly()
	{
		$RACH = new AgaviReturnArrayConfigHandler();
		$this->assertType('AgaviReturnArrayConfigHandler', $RACH);
		
		$cfg_array = include(AgaviConfigCache::checkConfig('config/RACHwithBools.ini'));
		$this->assertTrue(is_array($cfg_array));
		foreach ($cfg_array['truths'] as $key => $val) {
			$this->assertTrue(is_bool($val));
			$this->assertTrue($val);
		}
		foreach ($cfg_array['nots'] as $key => $val) {
			$this->assertTrue(is_bool($val));
			$this->assertFalse($val);
		}
		$this->assertTrue(is_bool($cfg_array['nots']['One']));
		$this->assertFalse($cfg_array['nots']['One']);
	}

}
?>
<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class ReturnArrayConfigHandlerTest extends UnitTestCase
{
	public function testParseSimpleIniFileIntoArray()
	{
		$RACH = new ReturnArrayConfigHandler();
		$this->assertIsA($RACH, 'ReturnArrayConfigHandler');
		$simple = $RACH->execute(AG_CONFIG_DIR . '/RACHsimple.ini');
		$simple_array = array(
			'section1' => array('One' => 'A', 'Two' => 'B', 'Three' => 'C'), 
			'section2' => array('Three' => 'Z', 'Two' => 'Y', 'One' => 'X'));
		$ex_simple = '<?php return '. var_export($simple_array, true) .';?>';
		$this->assertIdentical($simple, $ex_simple);
	}

	public function testParseDottedIniFileIntoNestedArray()
	{
		$RACH = new ReturnArrayConfigHandler();
		$this->assertIsA($RACH, 'ReturnArrayConfigHandler');
		
		$dotted = $RACH->execute(AG_CONFIG_DIR . '/RACHwithDots.ini');
		$dotted_array = array(
			'one' => array(
				'type' => 'associative',
				'sub' => array(
					'a' => 'apple',
					'b' => 'bubble',
					'c' => 'candy')),
			'two' => array(
				'type' => 'numeric',
				'sub' => array('dot', 'dot dot', 'dot dot dot')),
			'three' => array(
				'type' => 'withdots',
				'three.sub' => array(
					'a' => 'A',
					'b' => 'B')),
			'four' => array(
				'type' => 'moredots',
				'four.sub' => array('dot', 'dot dot')));
		$ex_dotted = '<?php return '. var_export($dotted_array, true) .';?>';
		$this->assertIdentical($dotted, $ex_dotted);
	}
}
?>

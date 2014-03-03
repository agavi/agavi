<?php

class AgaviDecimalFormatterTest extends AgaviPhpUnitTestCase
{
	/**
	 * @dataProvider getParseData
	 */
	public function testParse($input, $output, $expectExtraChars = false, $maxIcuVersion = null)
	{
		$hasExtraChars = false;
		$parsed = AgaviDecimalFormatter::parse($input, null, $hasExtraChars);
		
		$this->assertEquals($output, $parsed);
		$this->assertEquals($expectExtraChars, $hasExtraChars);
	}
	
	protected function getIcuVersion() {
		static $icuVersion = null;
		
		if(defined('INTL_ICU_VERSION')) {
			return INTL_ICU_VERSION;
		}
		
		if($icuVersion === null) {
			$icuVersion = 0;
			$ext = new ReflectionExtension('intl');
			ob_start();
			$ext->info();
			$info = ob_get_contents();
			if(preg_match('/ICU Version => (.*)/i', $info, $match)) {
				$icuVersion = $match[1];
			}
			ob_end_clean();
		}
		
		return $icuVersion;
	}
	/**
	 * @dataProvider getParseData
	 */
	public function testNumberFormatter($input, $output, $expectExtraChars = false, $maxIcuVersion = null)
	{
		if(!class_exists('NumberFormatter')) {
			$this->markTestSkipped('ext/intl not loaded');
			return;
		}
		
		$icuVersion = $this->getIcuVersion();
		if($maxIcuVersion && version_compare($icuVersion, $maxIcuVersion, '>')) {
			$this->markTestSkipped('ICU Version to big for this test. Version is ' . $icuVersion . ' max allowed ' . $maxIcuVersion);
			return;
		}
		
		
		$input = trim($input);
		$yay = 0;
		
		$x = new NumberFormatter("en_US", NumberFormatter::DECIMAL);
		$x->setAttribute(NumberFormatter::LENIENT_PARSE, true);
		$parsed = $x->parse($input, NumberFormatter::TYPE_DOUBLE, $yay);
		
		$this->assertEquals($output, $parsed);
		$this->assertEquals($expectExtraChars, $yay < strlen($input));
	}
	
	public function getParseData()
	{
		return array(
			array(
				'0',
				0,
			),
			array(
				'00',
				0,
			),
			array(
				'010',
				10,
			),
			array(
				'1',
				1,
			),
			array(
				'01',
				01,
			),
			array(
				'1.1',
				1.1,
			),
			array(
				'0.1',
				0.1,
			),
			array(
				'0.01',
				0.01,
			),
			array(
				'0.001',
				0.001,
			),
			array(
				'1.2',
				1.2,
			),
			array(
				'1.02',
				1.02,
			),
			array(
				'1.002',
				1.002,
			),
			array(
				'10',
				10,
			),
			array(
				'10.1',
				10.1,
			),
			array(
				'10.',
				10,
			),
			array(
				'.1',
				0.1,
			),
			array(
				'-0',
				0,
			),
			array(
				'-00',
				0,
			),
			array(
				'-1',
				-1,
			),
			array(
				'-01',
				-1,
			),
			array(
				'-0.1',
				-0.1,
			),
			array(
				'-1.1',
				-1.1,
			),
			array(
				'-1.',
				-1,
			),
			array(
				'-0.',
				0,
			),
			array(
				'-.1',
				-0.1,
			),
			array(
				'.',
				false,
				true,
			),
			array(
				'3,.3',
				3.3,
			),
			array(
				'-.,1',
				-0.1,
				false,
				'4.0'
			),
			array(
				'',
				false,
			),
			array(
				'1.2a',
				1.2,
				true,
			),
			array(
				'a1.2',
				false,
				true,
			),
			array(
				' 1.2',
				1.2,
			),
			array(
				'1.2 ',
				1.2,
			),
			array(
				' 1.2 ',
				1.2,
			),
			array(
				'1.2.',
				1.2,
				true,
			),
			array(
				'.,',
				false,
				true,
			),
			array(
				',.',
				false,
				true,
			),
			array(
				'1,1,',
				11,
				true,
			),
			array(
				'1,1,.',
				11,
				true,
			),
			array(
				'1,1.',
				11,
				false,
			),
			array(
				'1,1.,',
				11,
				true,
			),
			array(
				'3,.,3',
				3.3,
				false,
				'4.0'
			),
			array(
				',3.,3',
				3.3,
				false,
				'4.0'
			),
		);
	}
}


?>
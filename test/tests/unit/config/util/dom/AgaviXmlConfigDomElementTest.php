<?php

class AgaviXmlConfigDomElementTest extends AgaviPhpunitTestCase
{
	protected $doc;
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		
		$this->doc = new AgaviXmlConfigDomDocument();
		$this->doc->appendChild($this->doc->createElementNs('http://agavi.org/agavi/config/global/envelope/1.1', 'configurations')); // so we can test getAgaviParameters()
	}
	
	/**
	 * @dataProvider genGetLiteralValueCases
	 */
	public function testGetLiteralValue($xmlPart, $expected)
	{
		$element = $this->doc->createDocumentFragment();
		$this->assertEquals(true, $element->appendXML($x = '<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . $xmlPart . '</foo>'));
		$this->assertEquals($expected, $element->firstChild->firstChild->getLiteralValue());
	}
	
	/**
	 * @dataProvider genGetLiteralValueCases
	 */
	public function testGetAgaviParametersLiteralizes($xmlPart, $expected)
	{
		$element = $this->doc->createDocumentFragment();
		$this->assertEquals(true, $element->appendXML('<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . $xmlPart . '</foo>'));
		$this->assertEquals(array('foo' => $expected), $element->firstChild->getAgaviParameters());
	}
	
	/**
	 * @dataProvider genGetAgaviParametersBasicsCases
	 */
	public function testGetAgaviParametersBasics($xmlPart, $expected)
	{
		$element = $this->doc->createDocumentFragment();
		$this->assertEquals(true, $element->appendXML('<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . $xmlPart . '</foo>'));
		$this->assertEquals($expected, $element->firstChild->getAgaviParameters());
	}
	
	/**
	 * @dataProvider genGetAgaviParametersMergeCases
	 */
	public function testGetAgaviParametersInternalMerge(array $xmlParts, $expected)
	{
		$element = $this->doc->createDocumentFragment();
		$this->assertEquals(true, $element->appendXML('<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . implode('', $xmlParts) . '</foo>'));
		$this->assertEquals($expected, $element->firstChild->getAgaviParameters());
	}
	
	/**
	 * @dataProvider genGetAgaviParametersMergeCases
	 */
	public function testGetAgaviParametersExternalMerge(array $xmlParts, $expected)
	{
		$out = array();
		foreach($xmlParts as $xmlPart) {
			$element = $this->doc->createDocumentFragment();
			$this->assertEquals(true, $element->appendXML('<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . $xmlPart . '</foo>'));
			$out = $element->firstChild->getAgaviParameters($out);
		}
		$this->assertEquals($expected, $out);
	}
	
	public function genGetLiteralValueCases()
	{
		$sl = 'with xml:space="default" and ae:literalize="true"';
		$sL = 'with xml:space="default" and ae:literalize="false"';
		$Sl = 'with xml:space="preserve" and ae:literalize="true"';
		$SL = 'with xml:space="preserve" and ae:literalize="false"';
		return array(
			'simple content'              => array('<ae:parameter name="foo">bar</ae:parameter>', 'bar'),
			'whitespace trimming 1'       => array('<ae:parameter name="foo"> bar</ae:parameter>', 'bar'),
			'whitespace trimming 2'       => array('<ae:parameter name="foo"> bar</ae:parameter>', 'bar'),
			'whitespace trimming 3'       => array('<ae:parameter name="foo"> bar </ae:parameter>', 'bar'),
			'whitespace trimming 4'       => array('<ae:parameter name="foo"> bar   </ae:parameter>', 'bar'),
			'empty element'               => array('<ae:parameter name="foo"></ae:parameter>', null),
			'self-closing element'        => array('<ae:parameter name="foo" />', null),
			'whitespace only'             => array('<ae:parameter name="foo">  </ae:parameter>', null),
			'true 1'                      => array('<ae:parameter name="foo">true</ae:parameter>', true),
			'true 2'                      => array('<ae:parameter name="foo"> true</ae:parameter>', true),
			'true 3'                      => array('<ae:parameter name="foo">true </ae:parameter>', true),
			'true 4'                      => array('<ae:parameter name="foo"> true </ae:parameter>', true),
			'true 5'                      => array('<ae:parameter name="foo">TRUE</ae:parameter>', true),
			'true 6'                      => array('<ae:parameter name="foo">True</ae:parameter>', true),
			'true 7'                      => array('<ae:parameter name="foo">trUe</ae:parameter>', true),
			'yes 1'                       => array('<ae:parameter name="foo">yes</ae:parameter>', true),
			'yes 2'                       => array('<ae:parameter name="foo"> yes</ae:parameter>', true),
			'yes 3'                       => array('<ae:parameter name="foo">yes </ae:parameter>', true),
			'yes 4'                       => array('<ae:parameter name="foo"> yes </ae:parameter>', true),
			'yes 5'                       => array('<ae:parameter name="foo">YES</ae:parameter>', true),
			'yes 6'                       => array('<ae:parameter name="foo">Yes</ae:parameter>', true),
			'yes 7'                       => array('<ae:parameter name="foo">yeS</ae:parameter>', true),
			'on 1'                        => array('<ae:parameter name="foo">on</ae:parameter>', true),
			'on 2'                        => array('<ae:parameter name="foo"> on</ae:parameter>', true),
			'on 3'                        => array('<ae:parameter name="foo">on </ae:parameter>', true),
			'on 4'                        => array('<ae:parameter name="foo"> on </ae:parameter>', true),
			'on 5'                        => array('<ae:parameter name="foo">ON</ae:parameter>', true),
			'on 6'                        => array('<ae:parameter name="foo">On</ae:parameter>', true),
			'on 7'                        => array('<ae:parameter name="foo">oN</ae:parameter>', true),
			'false 1'                     => array('<ae:parameter name="foo">false</ae:parameter>', false),
			'false 2'                     => array('<ae:parameter name="foo"> false</ae:parameter>', false),
			'false 3'                     => array('<ae:parameter name="foo">false </ae:parameter>', false),
			'false 4'                     => array('<ae:parameter name="foo"> false </ae:parameter>', false),
			'false 5'                     => array('<ae:parameter name="foo">FALSE</ae:parameter>', false),
			'false 6'                     => array('<ae:parameter name="foo">False</ae:parameter>', false),
			'false 7'                     => array('<ae:parameter name="foo">faLse</ae:parameter>', false),
			'no 1'                        => array('<ae:parameter name="foo">no</ae:parameter>', false),
			'no 2'                        => array('<ae:parameter name="foo"> no</ae:parameter>', false),
			'no 3'                        => array('<ae:parameter name="foo">no </ae:parameter>', false),
			'no 4'                        => array('<ae:parameter name="foo"> no </ae:parameter>', false),
			'no 5'                        => array('<ae:parameter name="foo">NO</ae:parameter>', false),
			'no 6'                        => array('<ae:parameter name="foo">No</ae:parameter>', false),
			'no 7'                        => array('<ae:parameter name="foo">nO</ae:parameter>', false),
			'off 1'                       => array('<ae:parameter name="foo">off</ae:parameter>', false),
			'off 2'                       => array('<ae:parameter name="foo"> off</ae:parameter>', false),
			'off 3'                       => array('<ae:parameter name="foo">off </ae:parameter>', false),
			'off 4'                       => array('<ae:parameter name="foo"> off </ae:parameter>', false),
			'off 5'                       => array('<ae:parameter name="foo">OFF</ae:parameter>', false),
			'off 6'                       => array('<ae:parameter name="foo">Off</ae:parameter>', false),
			'off 7'                       => array('<ae:parameter name="foo">oFF</ae:parameter>', false),
			'existing directive 1'        => array('<ae:parameter name="foo">%core.agavi_dir%</ae:parameter>', AgaviConfig::get('core.agavi_dir')),
			'existing directive 2'        => array('<ae:parameter name="foo">%core.agavi_dir% </ae:parameter>', AgaviConfig::get('core.agavi_dir')),
			'non-existing directive 1'    => array('<ae:parameter name="foo">%asjduz81279iugahjsd%</ae:parameter>', '%asjduz81279iugahjsd%'),
			'non-existing directive 2'    => array('<ae:parameter name="foo">%asjduz81279iugahjsd% </ae:parameter>', '%asjduz81279iugahjsd%'),
			'multiple directives'         => array('<ae:parameter name="foo">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd%</ae:parameter>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd%'),
			
			"simple content $sl"          => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">bar</ae:parameter>', 'bar'),
			"whitespace trimming $sl"     => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true"> bar</ae:parameter>', 'bar'),
			"empty element $sl"           => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true"></ae:parameter>', null),
			"self-closing element $sl"    => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true" />', null),
			"whitespace only $sl"         => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">  </ae:parameter>', null),
			"true $sl"                    => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">true</ae:parameter>', true),
			"true +ws $sl"                => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">true </ae:parameter>', true),
			"existing directive $sl"      => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">%core.agavi_dir%</ae:parameter>', AgaviConfig::get('core.agavi_dir')),
			"existing directive +ws $sl"  => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true"> %core.agavi_dir%</ae:parameter>', AgaviConfig::get('core.agavi_dir')),
			"unknown directive $sl"       => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">%asjduz81279iugahjsd%</ae:parameter>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $sl" => array('<ae:parameter name="foo" xml:space="default" ae:literalize="true">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </ae:parameter>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd%'),
			
			"simple content $sL"          => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">bar</ae:parameter>', 'bar'),
			"whitespace trimming $sL"     => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false"> bar</ae:parameter>', 'bar'),
			"empty element $sL"           => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false"></ae:parameter>', ''),
			"self-closing element $sL"    => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false" />', ''),
			"whitespace only $sL"         => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">  </ae:parameter>', ''),
			"true $sL"                    => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">true</ae:parameter>', 'true'),
			"true +ws $sL"                => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">true </ae:parameter>', 'true'),
			"existing directive $sL"      => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">%core.agavi_dir%</ae:parameter>', '%core.agavi_dir%'),
			"existing directive +ws $sL"  => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false"> %core.agavi_dir%</ae:parameter>', '%core.agavi_dir%'),
			"unknown directive $sL"       => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">%asjduz81279iugahjsd%</ae:parameter>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $sL" => array('<ae:parameter name="foo" xml:space="default" ae:literalize="false">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </ae:parameter>', '%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd%'),
			
			"simple content $Sl"          => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">bar</ae:parameter>', 'bar'),
			"whitespace trimming $Sl"     => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true"> bar</ae:parameter>', ' bar'),
			"empty element $Sl"           => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true"></ae:parameter>', null),
			"self-closing element $Sl"    => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true" />', null),
			"whitespace only $Sl"         => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">  </ae:parameter>', '  '),
			"true $Sl"                    => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">true</ae:parameter>', true),
			"true +ws $Sl"                => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">true </ae:parameter>', 'true '),
			"existing directive $Sl"      => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">%core.agavi_dir%</ae:parameter>', AgaviConfig::get('core.agavi_dir')),
			"existing directive +ws $Sl"  => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true"> %core.agavi_dir%</ae:parameter>', ' ' . AgaviConfig::get('core.agavi_dir')),
			"unknown directive $Sl"       => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">%asjduz81279iugahjsd%</ae:parameter>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $Sl" => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="true">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </ae:parameter>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd% '),
			
			"simple content $SL"          => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">bar</ae:parameter>', 'bar'),
			"whitespace trimming $SL"     => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false"> bar</ae:parameter>', ' bar'),
			"empty element $SL"           => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false"></ae:parameter>', ''),
			"self-closing element $SL"    => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false" />', ''),
			"whitespace only $SL"         => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">  </ae:parameter>', '  '),
			"true $SL"                    => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">true</ae:parameter>', 'true'),
			"true +ws $SL"                => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">true </ae:parameter>', 'true '),
			"existing directive $SL"      => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">%core.agavi_dir%</ae:parameter>', '%core.agavi_dir%'),
			"existing directive +ws $SL"  => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false"> %core.agavi_dir%</ae:parameter>', ' %core.agavi_dir%'),
			"unknown directive $SL"       => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">%asjduz81279iugahjsd%</ae:parameter>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $SL" => array('<ae:parameter name="foo" xml:space="preserve" ae:literalize="false">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </ae:parameter>', '%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% '),
		);
	}
	
	public function genGetAgaviParametersBasicsCases()
	{
		return array(
			'simple element'                     => array('<ae:parameter name="foo">bar</ae:parameter>', array('foo' => 'bar')),
			'nested element'                     => array('<ae:parameter name="foo"><ae:parameter name="bar">baz</ae:parameter></ae:parameter>', array('foo' => array('bar' => 'baz'))),
			'multiple elements'                  => array('<ae:parameter name="foo">bar</ae:parameter><ae:parameter name="bar">baz</ae:parameter>', array('foo' => 'bar', 'bar' => 'baz')),
			'numeric array'                      => array('<ae:parameter name="foo"><ae:parameter>bar</ae:parameter><ae:parameter>baz</ae:parameter></ae:parameter>', array('foo' => array('bar', 'baz'))),
			'numeric keys offset 0'              => array('<ae:parameter name="foo"><ae:parameter name="0">bar</ae:parameter><ae:parameter name="1">baz</ae:parameter></ae:parameter>', array('foo' => array('bar', 'baz'))),
			'numeric keys offset 5'              => array('<ae:parameter name="foo"><ae:parameter name="5">bar</ae:parameter><ae:parameter name="6">baz</ae:parameter></ae:parameter>', array('foo' => array(5 => 'bar', 6 => 'baz'))),
			'numeric keys offset and auto index' => array('<ae:parameter name="foo"><ae:parameter name="5">bar</ae:parameter><ae:parameter>baz</ae:parameter></ae:parameter>', array('foo' => array(5 => 'bar', 0 => 'baz'))),
			'plural container'                   => array('<ae:parameters><ae:parameter name="foo">bar</ae:parameter></ae:parameters>', array('foo' => 'bar')),
			'plural children'                    => array('<ae:parameters><ae:parameter name="foo"><ae:parameters><ae:parameter>bar</ae:parameter></ae:parameters></ae:parameter></ae:parameters>', array('foo' => array(0 => 'bar'))),
			'plural and singular mix'            => array('<ae:parameters><ae:parameter name="foo">bar</ae:parameter></ae:parameters><ae:parameter name="bar">baz</ae:parameter>', array('foo' => 'bar', 'bar' => 'baz')),
		);
	}
	
	public function genGetAgaviParametersMergeCases()
	{
		return array(
			'simple overwrite'                   => array(array('<ae:parameter name="foo">bar</ae:parameter>', '<ae:parameter name="foo">baz</ae:parameter>'), array('foo' => 'baz')),
			'singular/plural overwrite'          => array(array('<ae:parameter name="foo">bar</ae:parameter>', '<ae:parameters><ae:parameter name="foo">baz</ae:parameter></ae:parameters>'), array('foo' => 'baz')),
			'overwrite array with string'        => array(array('<ae:parameter name="foo"><ae:parameter>bar</ae:parameter></ae:parameter>', '<ae:parameter name="foo">baz</ae:parameter>'), array('foo' => 'baz')),
			'overwrite string with array'        => array(array('<ae:parameter name="foo">baz</ae:parameter>', '<ae:parameter name="foo"><ae:parameter>bar</ae:parameter></ae:parameter>'), array('foo' => array('bar'))),
			'overwrite string with array w/ key' => array(array('<ae:parameter name="foo">baz</ae:parameter>', '<ae:parameter name="foo"><ae:parameter name="1">bar</ae:parameter></ae:parameter>'), array('foo' => array(1 => 'bar'))),
			'numeric keys are not reindexed'     => array(array('<ae:parameter name="foo"><ae:parameter>bar</ae:parameter></ae:parameter>', '<ae:parameter name="foo"><ae:parameter>baz</ae:parameter></ae:parameter>'), array('foo' => array(0 => 'baz'))),
			'empty element overwrites'           => array(array('<ae:parameter name="foo">bar</ae:parameter>', '<ae:parameter name="foo"></ae:parameter>'), array('foo' => null)),
		);
	}
}
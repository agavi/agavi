<?php

class AgaviXmlConfigDomElementTest extends AgaviPhpunitTestCase
{
	protected $doc;
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		
		$this->doc = new AgaviXmlConfigDomDocument();
	}
	
	/**
	 * @dataProvider genLiteralValueCases
	 */
	public function testLiteralValue($xmlPart, $expected)
	{
		$element = $this->doc->createDocumentFragment();
		$this->assertEquals(true, $element->appendXML('<foo xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">' . $xmlPart . '</foo>'));
		foreach($element->firstChild->childNodes as $node) {
			$this->assertEquals($expected, $node->getLiteralValue());
		}
	}
	
	public function genLiteralValueCases()
	{
		$sl = 'with xml:space="default" and ae:literalize="true"';
		$sL = 'with xml:space="default" and ae:literalize="false"';
		$Sl = 'with xml:space="preserve" and ae:literalize="true"';
		$SL = 'with xml:space="preserve" and ae:literalize="false"';
		return array(
			'simple content'             => array('<x>bar</x>', 'bar'),
			'whitespace trimming'        => array('<x> bar</x><x> bar </x><x> bar </x><x> bar   </x>', 'bar'),
			'empty element'              => array('<x></x>', null),
			'whitespace only'            => array('<x>  </x>', null),
			'true'                       => array('<x>true</x><x> true</x><x>true </x><x> true </x><x>TRUE</x><x>True</x><x>trUe</x>', true),
			'yes'                        => array('<x>yes</x><x> yes</x><x>yes </x><x> yes </x><x>YES</x><x>Yes</x><x>yeS</x>', true),
			'on'                         => array('<x>on</x><x> on</x><x>on </x><x> on </x><x>ON</x><x>On</x><x>oN</x>', true),
			'false'                      => array('<x>false</x><x> false</x><x>false </x><x> false </x><x>FALSE</x><x>False</x><x>faLse</x>', false),
			'no'                         => array('<x>no</x><x> no</x><x>no </x><x> no </x><x>NO</x><x>No</x><x>nO</x>', false),
			'off'                        => array('<x>off</x><x> off</x><x>off </x><x> off </x><x>OFF</x><x>Off</x><x>oFF</x>', false),
			'existing directive'         => array('<x>%core.agavi_dir%</x><x>%core.agavi_dir% </x>', AgaviConfig::get('core.agavi_dir')),
			'non-existing directive'     => array('<x>%asjduz81279iugahjsd%</x><x>%asjduz81279iugahjsd% </x>', '%asjduz81279iugahjsd%'),
			'multiple directives'        => array('<x>%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd%</x>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd%'),
			
			"simple content $sl"          => array('<x xml:space="default" ae:literalize="true">bar</x>', 'bar'),
			"whitespace trimming $sl"     => array('<x xml:space="default" ae:literalize="true"> bar</x>', 'bar'),
			"empty element $sl"           => array('<x xml:space="default" ae:literalize="true"></x>', null),
			"whitespace only $sl"         => array('<x xml:space="default" ae:literalize="true">  </x>', null),
			"true $sl"                    => array('<x xml:space="default" ae:literalize="true">true</x>', true),
			"true +ws $sl"                => array('<x xml:space="default" ae:literalize="true">true </x>', true),
			"existing directive $sl"      => array('<x xml:space="default" ae:literalize="true">%core.agavi_dir%</x>', AgaviConfig::get('core.agavi_dir')),
			"existing directive +ws $sl"  => array('<x xml:space="default" ae:literalize="true"> %core.agavi_dir%</x>', AgaviConfig::get('core.agavi_dir')),
			"unknown directive $sl"       => array('<x xml:space="default" ae:literalize="true">%asjduz81279iugahjsd%</x>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $sl" => array('<x xml:space="default" ae:literalize="true">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </x>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd%'),
			
			"simple content $sL"          => array('<x xml:space="default" ae:literalize="false">bar</x>', 'bar'),
			"whitespace trimming $sL"     => array('<x xml:space="default" ae:literalize="false"> bar</x>', 'bar'),
			"empty element $sL"           => array('<x xml:space="default" ae:literalize="false"></x>', ''),
			"whitespace only $sL"         => array('<x xml:space="default" ae:literalize="false">  </x>', ''),
			"true $sL"                    => array('<x xml:space="default" ae:literalize="false">true</x>', 'true'),
			"true +ws $sL"                => array('<x xml:space="default" ae:literalize="false">true </x>', 'true'),
			"existing directive $sL"      => array('<x xml:space="default" ae:literalize="false">%core.agavi_dir%</x>', '%core.agavi_dir%'),
			"existing directive +ws $sL"  => array('<x xml:space="default" ae:literalize="false"> %core.agavi_dir%</x>', '%core.agavi_dir%'),
			"unknown directive $sL"       => array('<x xml:space="default" ae:literalize="false">%asjduz81279iugahjsd%</x>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $sL" => array('<x xml:space="default" ae:literalize="false">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </x>', '%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd%'),
			
			"simple content $Sl"          => array('<x xml:space="preserve" ae:literalize="true">bar</x>', 'bar'),
			"whitespace trimming $Sl"     => array('<x xml:space="preserve" ae:literalize="true"> bar</x>', ' bar'),
			"empty element $Sl"           => array('<x xml:space="preserve" ae:literalize="true"></x>', null),
			"whitespace only $Sl"         => array('<x xml:space="preserve" ae:literalize="true">  </x>', '  '),
			"true $Sl"                    => array('<x xml:space="preserve" ae:literalize="true">true</x>', true),
			"true +ws $Sl"                => array('<x xml:space="preserve" ae:literalize="true">true </x>', 'true '),
			"existing directive $Sl"      => array('<x xml:space="preserve" ae:literalize="true">%core.agavi_dir%</x>', AgaviConfig::get('core.agavi_dir')),
			"existing directive +ws $Sl"  => array('<x xml:space="preserve" ae:literalize="true"> %core.agavi_dir%</x>', ' ' . AgaviConfig::get('core.agavi_dir')),
			"unknown directive $Sl"       => array('<x xml:space="preserve" ae:literalize="true">%asjduz81279iugahjsd%</x>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $Sl" => array('<x xml:space="preserve" ae:literalize="true">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </x>', AgaviConfig::get('core.agavi_dir') . '/Agavi/%asjduz81279iugahjsd% '),
			
			"simple content $SL"          => array('<x xml:space="preserve" ae:literalize="false">bar</x>', 'bar'),
			"whitespace trimming $SL"     => array('<x xml:space="preserve" ae:literalize="false"> bar</x>', ' bar'),
			"empty element $SL"           => array('<x xml:space="preserve" ae:literalize="false"></x>', ''),
			"whitespace only $SL"         => array('<x xml:space="preserve" ae:literalize="false">  </x>', '  '),
			"true $SL"                    => array('<x xml:space="preserve" ae:literalize="false">true</x>', 'true'),
			"true +ws $SL"                => array('<x xml:space="preserve" ae:literalize="false">true </x>', 'true '),
			"existing directive $SL"      => array('<x xml:space="preserve" ae:literalize="false">%core.agavi_dir%</x>', '%core.agavi_dir%'),
			"existing directive +ws $SL"  => array('<x xml:space="preserve" ae:literalize="false"> %core.agavi_dir%</x>', ' %core.agavi_dir%'),
			"unknown directive $SL"       => array('<x xml:space="preserve" ae:literalize="false">%asjduz81279iugahjsd%</x>', '%asjduz81279iugahjsd%'),
			"multiple directives +ws $SL" => array('<x xml:space="preserve" ae:literalize="false">%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% </x>', '%core.agavi_dir%/%agavi.name%/%asjduz81279iugahjsd% '),
		);
	}
}
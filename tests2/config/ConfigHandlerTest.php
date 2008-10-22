<?php
class MyTestConfigHandler extends AgaviConfigHandler
{
	public function execute($config, $context = null)
	{
		return '';
	}
}

class ConfigHandlerTest extends AgaviTestCase
{
	protected $ch = null;
	public function setUp()
	{
		$this->ch = new MyTestConfigHandler();
		$this->ch->initialize('MyValidationFile.mvf');
	}

	public function tearDown()
	{
		$this->ch = null;
	}

	public function testGetValidationFile()
	{
		$this->assertSame('MyValidationFile.mvf', $this->ch->getValidationFile());
	}

	public function testLiteralize()
	{
		$ch = $this->ch;
		$this->assertNull(		$ch->literalize(null)						);

		$this->assertTrue(							$ch->literalize('true')					);
		$this->assertTrue(							$ch->literalize('trUe')					);
		$this->assertTrue(							$ch->literalize('On')						);
		$this->assertTrue(							$ch->literalize('on')						);
		$this->assertTrue(							$ch->literalize('yes')					);

		$this->assertFalse(							$ch->literalize('false')				);
		$this->assertFalse(							$ch->literalize('faLse')				);
		$this->assertFalse(							$ch->literalize('oFF')					);
		$this->assertFalse(							$ch->literalize('off')					);
		$this->assertFalse(							$ch->literalize('no')						);

		AgaviConfig::set('foo', 'foo');
		AgaviConfig::set('bar', 'bar');
		$this->assertSame('foo bar',		$ch->literalize('foo bar')			);
		$this->assertSame('foo bar',		$ch->literalize(' foo bar ')		);
		$this->assertSame('foo bar',		$ch->literalize('%foo% bar')		);
		$this->assertSame('foo bar',		$ch->literalize('%foo% %bar%')	);
		$this->assertSame('foo',				$ch->literalize('%foo%')				);
		$this->assertSame('foobar',			$ch->literalize('%foo%%bar%')		);
		$this->assertSame('foo %baz%',	$ch->literalize('%foo% %baz%')	);

		AgaviConfig::remove('foo');
		AgaviConfig::remove('bar');
	}

	public function testReplaceConstants()
	{
		AgaviConfig::set('foo', 'foo');
		AgaviConfig::set('bar', 'bar');
		AgaviConfig::set('foobar', '%foo% %bar%');
		AgaviConfig::set('foobarbaz', '%foobar% baz');

		$this->assertSame('foo',					AgaviToolkit::expandDirectives('%foo%')						);
		$this->assertSame('foo bar',			AgaviToolkit::expandDirectives('%foobar%')				);
		$this->assertSame('foo bar baz',	AgaviToolkit::expandDirectives('%foobar% baz')		);
		$this->assertSame('foo bar baz',	AgaviToolkit::expandDirectives('%foobarbaz%')			);


		AgaviConfig::remove('foo');
		AgaviConfig::remove('bar');
		AgaviConfig::remove('foobar');
		AgaviConfig::remove('foobarbaz');
	}


	public function testReplacePath()
	{

	}

}

<?php

class LALA_MOM {}
class LALA_KID extends LALA_MOM {}
class LALA_GRANDKID extends LALA_KID {}

class ToolkitTest extends AgaviTestCase
{
	public function testClassHeritage()
	{
		$this->assertEquals(array('LALA_MOM'), AgaviToolkit::classHeritage('LALA_KID'));
		$this->assertEquals(array('LALA_MOM', 'LALA_KID'), AgaviToolkit::classHeritage('LALA_GRANDKID'));
		$this->assertEquals(array(), AgaviToolkit::classHeritage('LALA_MOM'));

	}
	
	public function testSubClassOf()
	{
		$this->assertTrue(AgaviToolkit::isSubClass('LALA_KID', 'LALA_MOM'));
		$this->assertTrue(AgaviToolkit::isSubClass('LALA_GRANDKID', 'LALA_MOM'));
		
		$this->assertFalse(AgaviToolkit::isSubClass('LALA_MOM', 'LALA_MOM'));
		$this->assertFalse(AgaviToolkit::isSubClass('LALA_MOM', 'LALA_KID'));
		
	}

	public function testIsPathAbsolute()
	{
		$this->assertTrue(AgaviToolkit::isPathAbsolute('c:/'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('c:/Windows'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('g:/Windows/bar'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('c:\\windows\\foo'));
		// UNC paths are absolute too
		$this->assertTrue(AgaviToolkit::isPathAbsolute('\\\\some.host'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('\\\\some.host\\foo'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('/'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('/root'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('/FoO/bAR'));
		// shouldn't these 2 result in false (does php support backslashes on *nix?)
		$this->assertTrue(AgaviToolkit::isPathAbsolute('\\foo'));
		$this->assertTrue(AgaviToolkit::isPathAbsolute('\\foo\\bar'));

		$this->assertfalse(AgaviToolkit::isPathAbsolute('c:'));
		$this->assertFalse(AgaviToolkit::isPathAbsolute('s/foo/bar'));
		$this->assertFalse(AgaviToolkit::isPathAbsolute('c:foo'));
	}

	public function testExtractClassName()
	{
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('AgaviInterface.interface.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('/AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('Interface/AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('/Interface/AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('/Interface//AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('c:\\windows\\AgaviInterface.class.php'));
		$this->assertEquals('AgaviInterface', AgaviToolkit::extractClassName('\\AgaviInterface.class.php'));
	}
}

?>
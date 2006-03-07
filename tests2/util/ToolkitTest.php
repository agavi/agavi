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
}

?>
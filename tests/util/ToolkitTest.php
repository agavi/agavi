<?php

class LALA_MOM {}
class LALA_KID extends LALA_MOM {}
class LALA_GRANDKID extends LALA_KID {}

class ToolkitTest extends UnitTestCase
{
	public function testClassHeritage()
	{
		$this->assertIdentical(array('LALA_MOM'), Toolkit::classHeritage('LALA_KID'));
		$this->assertIdentical(array('LALA_MOM', 'LALA_KID'), Toolkit::classHeritage('LALA_GRANDKID'));
		$this->assertIdentical(array(), Toolkit::classHeritage('LALA_MOM'));

	}
	
	public function testSubClassOf()
	{
		$this->assertTrue(Toolkit::isSubClass('LALA_KID', 'LALA_MOM'));
		$this->assertTrue(Toolkit::isSubClass('LALA_GRANDKID', 'LALA_MOM'));
		
		$this->assertFalse(Toolkit::isSubClass('LALA_MOM', 'LALA_MOM'));
		$this->assertFalse(Toolkit::isSubClass('LALA_MOM', 'LALA_KID'));
		
	}
}

?>
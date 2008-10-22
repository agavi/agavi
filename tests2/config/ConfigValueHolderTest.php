<?php

class ConfigValueHolderTest extends AgaviTestCase
{
	public function testInitialState()
	{
		$vh = new AgaviConfigValueHolder();
		$this->assertSame('', $vh->getName());
		$this->assertSame(array(), $vh->getAttributes());
		$this->assertSame(array(), $vh->getChildren());
		$this->assertNull($vh->getValue());
	}

	public function testSetName()
	{
		$vh = new AgaviConfigValueHolder();
		$vh->setName('test1');
		$this->assertSame('test1', $vh->getName());
		$vh->setName('test2');
		$this->assertSame('test2', $vh->getName());
	}

	public function testAddChildren()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();

		$vh->addChildren('child1', $vhChild1);
		$vh->addChildren('child2', $vhChild2);
		$this->assertReference($vhChild1, $vh->child1);
		$this->assertReference($vhChild1, $vh['child1']);
		$this->assertReference($vhChild2, $vh->child2);
		$this->assertReference($vhChild2, $vh['child2']);
	}

	public function testAppendChildren()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();

		$vh->appendChildren($vhChild1);
		$vh->appendChildren($vhChild2);
		$a = $vh->getChildren();
		$this->assertReference($vhChild1, $a[0]);
		$this->assertReference($vhChild2, $a[1]);
	}

	public function testHasChildren()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();

		$this->assertFalse($vh->hasChildren());
		$this->assertFalse($vh->hasChildren('child1'));

		$vh->addChildren('child1', $vhChild1);

		$this->assertTrue($vh->hasChildren());
		$this->assertTrue($vh->hasChildren('child1'));
		$this->assertFalse($vh->hasChildren('child2'));
	}

	public function testGetChildren()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();
		$vhChild3 = new AgaviConfigValueHolder();

		$vh->addChildren('child1', $vhChild1);
		$vh->addChildren('child2', $vhChild2);
		$vh->addChildren('child3', $vhChild3);

		$childs = $vh->getChildren();

		$this->assertSame(3, count($childs));
		$this->assertReference($vhChild1, $childs['child1']);
		$this->assertReference($vhChild2, $childs['child2']);
		$this->assertReference($vhChild3, $childs['child3']);
	}

	public function testSetAttribute()
	{
		$vh = new AgaviConfigValueHolder();

		$vh->setAttribute('attr1', 'val1');
		$vh->setAttribute('attr2', 'val2');
		$this->assertSame('val1', $vh->getAttribute('attr1'));
		$this->assertSame('val2', $vh->getAttribute('attr2'));
		$vh->setAttribute('attr1', 'val3');
		$this->assertSame('val3', $vh->getAttribute('attr1'));
	}

	public function testHasAttribute()
	{
		$vh = new AgaviConfigValueHolder();

		$this->assertFalse($vh->hasAttribute('attr1'));
		$this->assertFalse($vh->hasAttribute('attr2'));
		$vh->setAttribute('attr1', 'val1');
		$vh->setAttribute('attr2', 'val2');
		$this->assertTrue($vh->hasAttribute('attr1'));
		$this->assertTrue($vh->hasAttribute('attr2'));
		$vh->setAttribute('attr1', 'val3');
		$this->assertTrue($vh->hasAttribute('attr1'));
		$this->assertTrue($vh->hasAttribute('attr2'));
	}

	public function testGetAttribute()
	{
		$vh = new AgaviConfigValueHolder();

		$this->assertNull($vh->getAttribute('attr1'));
		$this->assertSame('default1', $vh->getAttribute('attr1', 'default1'));
		$vh->setAttribute('attr1', 'val1');
		$vh->setAttribute('attr2', 'val2');
		$this->assertSame('val1', $vh->getAttribute('attr1', 'default1'));
		$this->assertSame('val1', $vh->getAttribute('attr1'));
		$this->assertSame('val2', $vh->getAttribute('attr2'));
	}

	public function testGetAttributes()
	{
		$vh = new AgaviConfigValueHolder();
		$vh->setAttribute('attr1', 'val1');
		$vh->setAttribute('attr2', 'val2');
		$vh->setAttribute('attr3', 'val3');

		$attr = $vh->getAttributes();
		$attr_ex = array('attr1' => 'val1', 'attr2' => 'val2', 'attr3' => 'val3');

		$this->assertSame($attr_ex, $attr);
	}

	public function testSetGetValue()
	{
		$vh = new AgaviConfigValueHolder();

		$vh->setValue('value');
		$this->assertSame('value', $vh->getValue());
		$vh->setValue('newvalue');
		$this->assertSame('newvalue', $vh->getValue());
	}

	public function testArrayInterface()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();

		$this->assertFalse(isset($vh['child1']));
		$this->assertNull($vh['child1']);
		$vh['child1'] = $vhChild1;
		$vh->addChildren('child2', $vhChild2);
		$this->assertTrue(isset($vh['child1']));
		$this->assertReference($vhChild1, $vh['child1']);
		$this->assertReference($vhChild2, $vh['child2']);
		unset($vh['child1']);
		$this->assertFalse(isset($vh['child1']));
		$this->assertNull($vh['child1']);
	}

	public function testIteratorIterface()
	{
		$vh = new AgaviConfigValueHolder();
		$vhChild1 = new AgaviConfigValueHolder();
		$vhChild2 = new AgaviConfigValueHolder();
		$vhChild3 = new AgaviConfigValueHolder();

		$vh->addChildren('child1', $vhChild1);
		$vh->addChildren('child2', $vhChild2);
		$vh->addChildren('child3', $vhChild3);

		$i = 1;
		foreach($vh as $name => $child) {
			$this->assertSame('child' . $i, $name);
			$this->assertReference(${'vhChild' . $i}, $child);
			++$i;
		}

		$vh2 = new AgaviConfigValueHolder();

		$vh2->appendChildren($vhChild1);
		$vh2->appendChildren($vhChild2);
		$vh2->appendChildren($vhChild3);

		$i = 0;
		foreach($vh2 as $id => $child) {
			$this->assertSame($i, $id);
			$this->assertReference(${'vhChild' . ($i + 1)}, $child);
			++$i;
		}

	}
}

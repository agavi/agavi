<?php
AgaviConfig::set('user.default_namespace', 'org.agavi', false);

class SampleUser extends User
{
	public function initialize($context, $parameters=null)
	{
		$this->context = $context;
		if ($parameters != null) {
			$this->parameters = array_merge($this->parameters, $parameters);
		}
		$this->attributes = array();
	}
}

class UserTest extends UnitTestCase
{
	private $_u = null;

	public function setUp()
	{
		$this->_u = new SampleUser();
		$context = Context::getInstance();
		$this->_u->initialize($context);
	}

	public function testclearAttributes()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->_u->setAttribute('blah2', 'blah2val');
		$this->_u->clearAttributes();
		$this->assertEqual(array(), $this->_u->getAttributeNames());
	}

	public function testgetAttribute()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
		$this->assertNull($this->_u->getAttribute('bunk'));

		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->assertNull($this->_u->getAttribute('bunk', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
	}

	public function testgetAttributeNames()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->_u->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_u->getAttributeNames());

		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'), $this->_u->getAttributeNames('some/other/namespace'));

		$this->assertEqual(array('blah', 'blah2'), $this->_u->getAttributeNames());

		$this->assertNull($this->_u->getAttributeNames('/bunk/namespace'));
	}

	public function testgetAttributeNamespace()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->_u->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_u->getAttributeNamespace());

		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'=>'otherblah'), $this->_u->getAttributeNamespace('some/other/namespace'));

		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_u->getAttributeNamespace());

		$this->assertNull($this->_u->getAttributeNamespace('/bunk/namespace'));
	}

	public function testgetAttributeNamespaces()
	{
		$this->assertEqual(array(), $this->_u->getAttributeNamespaces());
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertEqual(array(AgaviConfig::get('user.default_namespace')), $this->_u->getAttributeNamespaces());
		$this->_u->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertEqual(array(AgaviConfig::get('user.default_namespace'), 'some/other/namespace'), $this->_u->getAttributeNamespaces());
	}

	public function testgetAttributes()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->_u->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_u->getAttributes());

		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'=>'otherblah'), $this->_u->getAttributes('some/other/namespace'));

		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_u->getAttributes());

		$this->assertEqual(array(), $this->_u->getAttributes('/bunk/namespace'));
	}

	public function testgetContext()
	{
		$ctx_1 = Context::getInstance();
		$ctx_2 = $this->_u->getContext();
		$this->assertIsA($ctx_1, 'Context');
		$this->assertIsA($ctx_2, 'Context');
		$this->assertReference($ctx_1, $ctx_2);
	}

	public function testhasAttribute()
	{
		$this->assertFalse($this->_u->hasAttribute('blah'));
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertTrue($this->_u->hasAttribute('blah'));
		$this->assertFalse($this->_u->hasAttribute('bunk'));

		$this->assertFalse($this->_u->hasAttribute('blah', 'some/other/namespace'));
		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertTrue($this->_u->hasAttribute('blah', 'some/other/namespace'));
		$this->assertFalse($this->_u->hasAttribute('bunk', 'some/other/namespace'));
		$this->_u->removeAttribute('blah', 'some/other/namespace');

		$this->assertTrue($this->_u->hasAttribute('blah'));
	}

	public function testhasAttributeNamespace()
	{
		$this->assertFalse($this->_u->hasAttributeNamespace(AgaviConfig::get('user.default_namespace')));
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertTrue($this->_u->hasAttributeNamespace(AgaviConfig::get('user.default_namespace')));
		$this->assertFalse($this->_u->hasAttributeNamespace('some/other/namespace'));
		$this->_u->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertTrue($this->_u->hasAttributeNamespace('some/other/namespace'));
	}
	
	public function testinitialize()
	{
		$context = Context::getInstance();
		$user = new User();
		$user->initialize($context);
		$this->assertIsA($user, 'User');
		$u_ctx = $user->getContext();
		$this->assertReference($context, $u_ctx);
		$this->assertNull($user->getAttributeNames());
	}

	public function testnewInstance()
	{
		$user = User::newInstance('User');
		$this->assertIsA($user,'User');
	}

	public function testremoveAttribute()
	{
		$this->assertNull($this->_u->removeAttribute('blah'));
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_u->removeAttribute('blah'));
		$this->assertNull($this->_u->removeAttribute('blah'));
		$this->_u->setAttribute('blah', 'blahval');

		$this->assertNull($this->_u->removeAttribute('blah', 'some/other/namespace'));
		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_u->removeAttribute('blah', 'some/other/namespace'));
		$this->assertNull($this->_u->removeAttribute('blah', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_u->removeAttribute('blah'));
	}

	public function testremoveAttributeNamespace()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->_u->removeAttributeNamespace(AgaviConfig::get('user.default_namespace'));
		$this->assertFalse($this->_u->hasAttributeNamespace(AgaviConfig::get('user.default_namespace')));
	}

	public function testsetAttribute()
	{
		$this->_u->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));

		$this->_u->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_u->getAttribute('blah', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
	}

	public function testappendAttribute()
	{
		$this->_u->appendAttribute('blah', 'blahval');
		$this->assertEqual(array('blahval'), $this->_u->getAttribute('blah'));
		$this->_u->appendAttribute('blah', 'blahval2');
		$this->assertEqual(array('blahval','blahval2'), $this->_u->getAttribute('blah'));

		$this->_u->appendAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('otherblah'), $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->_u->appendAttribute('blah', 'otherblah2', 'some/other/namespace');
		$this->assertEqual(array('otherblah', 'otherblah2'), $this->_u->getAttribute('blah', 'some/other/namespace'));

		$this->assertEqual(array('blahval','blahval2'), $this->_u->getAttribute('blah'));
	}

	public function testsetAttributeByRef()
	{
		$myval = 'blahval';
		$this->_u->setAttributeByRef('blah', $myval);
		$this->assertReference($myval, $this->_u->getAttribute('blah'));

		$myval2 = 'otherblah';
		$this->_u->setAttributeByRef('blah', $myval2, 'some/other/namespace');
		$this->assertReference($myval2, $this->_u->getAttribute('blah', 'some/other/namespace'));

		$this->assertReference($myval, $this->_u->getAttribute('blah'));
	}

	public function testappendAttributeByRef()
	{
		$myval1 = 'jack';
		$myval2 = 'bill';
		$this->_u->appendAttributeByRef('blah', $myval1);
		$out = $this->_u->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->_u->appendAttributeByRef('blah', $myval2);
		$out = $this->_u->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->assertReference($myval2, $out[1]);

		$myval3 = 'jill';
		$myval4 = 'jane';
		$this->_u->appendAttributeByRef('blah', $myval3, 'some/other/namespace');
		$out = $this->_u->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$this->_u->appendAttributeByRef('blah', $myval4, 'some/other/namespace');
		$out = $this->_u->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$this->assertReference($myval4, $out[1]);

		$out = $this->_u->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
	}


	public function testsetAttributes()
	{
		$this->_u->setAttributes(array('blah'=>'blahval'));
		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
		$this->_u->setAttributes(array('blah2'=>'blah2val'));
		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_u->getAttribute('blah2'));

		$this->_u->setAttributes(array('blah'=>'otherblah'), 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->_u->setAttributes(array('blah2'=>'otherblah2'), 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->assertEqual('otherblah2', $this->_u->getAttribute('blah2', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_u->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_u->getAttribute('blah2'));
	}

	public function testsetAttributesByRef()
	{
		$myval1 = 'blah';
		$myval2 = 'blah2';
		$this->_u->setAttributes(array('blah'=>&$myval1));
		$this->assertReference($myval1, $this->_u->getAttribute('blah'));
		$this->_u->setAttributes(array('blah2'=>&$myval2));
		$this->assertReference($myval1, $this->_u->getAttribute('blah'));
		$this->assertReference($myval2, $this->_u->getAttribute('blah2'));

		$myval3 = 'blah';
		$myval4 = 'blah2';
		$this->_u->setAttributes(array('blah'=>&$myval3), 'some/other/namespace');
		$this->assertReference($myval3, $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->_u->setAttributes(array('blah2'=>&$myval4), 'some/other/namespace');
		$this->assertReference($myval3, $this->_u->getAttribute('blah', 'some/other/namespace'));
		$this->assertReference($myval4, $this->_u->getAttribute('blah2', 'some/other/namespace'));

		$this->assertReference($myval1, $this->_u->getAttribute('blah'));
		$this->assertReference($myval2, $this->_u->getAttribute('blah2'));
	}

}
?>
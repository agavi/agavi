<?php
class SampleUser extends AgaviUser
{
}

class AgaviUserTest extends AgaviUnitTestCase
{
	private $_u = null;

	public function setUp()
	{
		$this->_u = new SampleUser();
		$context = AgaviContext::getInstance('test');
		$this->_u->initialize($this->getContext());
	}

	public function testInitialize()
	{
		$ctx = $this->getContext();
		$u = $this->_u;

		$ctx_test = $u->getContext();
		$this->assertSame($ctx, $ctx_test);
		$this->assertEquals('org.agavi.user.User', $u->getStorageNamespace());

		$u->initialize($ctx, array('default_namespace' => 'default.test.ns', 'storage_namespace' => 'storage.test.ns'));
		$this->assertEquals('default.test.ns', $u->getDefaultNamespace());
		$this->assertEquals('storage.test.ns', $u->getStorageNamespace());

	}
}
?>
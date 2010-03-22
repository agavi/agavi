<?php

class ToolkitTest extends AgaviTestCase
{
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
		$this->assertFalse(AgaviToolkit::isPathAbsolute('\\foo'));
		$this->assertFalse(AgaviToolkit::isPathAbsolute('\\foo\\bar'));

		$this->assertfalse(AgaviToolkit::isPathAbsolute('c:'));
		$this->assertFalse(AgaviToolkit::isPathAbsolute('s/foo/bar'));
		$this->assertFalse(AgaviToolkit::isPathAbsolute('c:foo'));
	}
}

?>
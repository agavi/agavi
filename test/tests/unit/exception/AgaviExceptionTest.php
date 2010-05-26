<?php

class AgaviExceptionTest extends AgaviUnitTestCase
{
	public function highlightSnippets()
	{
		return array(
			'ticket1240' => array(
				'<?php
class Default_Admin_Widgets_MenuSuccessView extends AdsDefaultBaseView
{
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		$this->setupHtml($rd);
		ob_start();?>duda
 <?php
throw new Exception();
ob_end_clean();

	}
}
?>'
			),
			'empty' => array(
				'',
			),
			'empty with newline' => array(
				'
',
			),
			'template starting with PHP code' => array(
				'
				<?php echo $tm->_("Ohai", "default"); ?>
				<div />
				<?php echo $tm->_("Ohai", "default"); ?>
				'
			),
			'template starting with HTML code' => array(
				'
				<div />
				<?php echo $tm->_("Ohai", "default"); ?>
				'
			),
		);
	}
	
	/**
	 * @dataProvider highlightSnippets
	 */
	public function testFoo($code)
	{
		$highlighted = AgaviException::highlightString($code);
		$highlighted = "<ol>\n<li><code>" . implode("</code></li>\n<li><code>", $highlighted) . "</code></li>\n</ol>";

		$doc = new DOMDocument();

		$luie = libxml_use_internal_errors(true);
		$doc->loadXML($highlighted);
		$errors = libxml_get_errors();
		libxml_use_internal_errors($luie);
		
		$this->assertEquals(0, count($errors));
	}
}

?>
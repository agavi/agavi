<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class AgaviValidatorConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testTranslationDomainInheritance()
	{
		$VCH = new AgaviValidatorConfigHandler();
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/validators.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/validators.xsl',
			'test-translation-domain'
		);
		
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$this->includeCode($VCH->execute($document), array(
			'validationManager' => $vm
		));
		
		$this->assertSame('__NULL__', $vm->getChild('toplevel_simple')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('__NULL__', $vm->getChild('toplevel_empty')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('test-domain', $vm->getChild('toplevel_or')->getParameter('translation_domain'));
		$this->assertSame('__NULL__', $vm->getChild('toplevel_or')->getChild('or_child')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('test-domain', $vm->getChild('toplevel_param')->getParameter('translation_domain'));
	}
	
}
?>
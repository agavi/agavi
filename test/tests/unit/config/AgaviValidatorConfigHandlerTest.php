<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class AgaviValidatorConfigHandlerTest extends ConfigHandlerTestBase
{
	protected function createValidationManager($environment) {
		$VCH = new AgaviValidatorConfigHandler();
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/validators.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/validators.xsl',
			$environment
		);
		
		$vm = $this->getContext()->createInstanceFor('validation_manager');
		$this->includeCode($VCH->execute($document), array(
			'validationManager' => $vm
		));
		
		return $vm;
	}
	
	public function testTranslationDomainInheritance1_0Behaviour()
	{
		$vm = $this->createValidationManager('test-translation-domain-1.0-behaviour');
		
		$this->assertSame('__NULL__', $vm->getChild('toplevel_simple')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('__NULL__', $vm->getChild('toplevel_empty')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('test-domain', $vm->getChild('toplevel_or')->getParameter('translation_domain'));
		$this->assertSame('__NULL__', $vm->getChild('toplevel_or')->getChild('or_child')->getParameter('translation_domain', '__NULL__'));
		$this->assertSame('test-domain', $vm->getChild('toplevel_param')->getParameter('translation_domain'));
	}
	
	
	public function testTranslationDomainInheritance()
	{
		$vm = $this->createValidationManager('test-translation-domain');
		
		$this->assertSame('test-domain-toplevel', $vm->getChild('toplevel_simple')->getParameter('translation_domain'));
		$this->assertSame('__NULL__', $vm->getChild('toplevel_reset')->getParameter('translation_domain', '__NULL__'));
		
		$this->assertSame('test-domain-toplevel', $vm->getChild('toplevel_or')->getParameter('translation_domain'));
		$this->assertSame('test-domain-toplevel', $vm->getChild('toplevel_or')->getChild('or_child')->getParameter('translation_domain'));

		$this->assertSame('test-domain-param-or', $vm->getChild('toplevel_param_or')->getParameter('translation_domain'));
		$this->assertSame('test-domain-param-or', $vm->getChild('toplevel_param_or')->getChild('param_or_child')->getParameter('translation_domain'));

		$this->assertSame('test-domain-direct-or', $vm->getChild('toplevel_direct_or')->getParameter('translation_domain'));
		$this->assertSame('test-domain-direct-nested-or', $vm->getChild('toplevel_direct_or')->getChild('direct_or_child')->getParameter('translation_domain'));
		
	}
	
	public function testErrorsDefinedByValidationDefinition() {
		$vm = $this->createValidationManager('test-validator-definition-error-definition');
		$this->assertSame(array('' => 'error-generic', 'min' => 'error-min'), $vm->getChild('standalone-empty')->getErrorMessages());
		$this->assertSame(array('' => 'error-generic-validator1', 'min' => 'error-min'), $vm->getChild('standalone-with-errors-single')->getErrorMessages());
		$this->assertSame(array('' => 'error-generic-validator2', 'min' => 'error-min-validator2'), $vm->getChild('standalone-with-errors-multi')->getErrorMessages());

		$this->assertSame(array('' => 'error-generic-overwritten', 'min' => 'error-min-overwritten'), $vm->getChild('overwritten-empty')->getErrorMessages());
		$this->assertSame(array('' => 'error-generic-validator3', 'min' => 'error-min-overwritten'), $vm->getChild('overwritten-with-errors-single')->getErrorMessages());
		$this->assertSame(array('' => 'error-generic-validator4', 'min' => 'error-min-validator4'), $vm->getChild('overwritten-with-errors-multi')->getErrorMessages());
	}
	
}
?>
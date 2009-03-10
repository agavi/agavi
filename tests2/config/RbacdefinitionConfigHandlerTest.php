<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class RbacDefinitionConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testHandler()
	{
		// taken from a compiled config_handlers.xml
		$handlerInfo = array (
			'class' => 'AgaviRbacDefinitionConfigHandler',
			'parameters' => 
			array (
			),
			'transformations' => 
			array (
				'single' => 
				array (
					0 => AgaviConfig::get('core.agavi_dir') . '/config/xsl/rbac_definitions.xsl',
				),
				'compilation' => 
				array (
				),
			),
			'validations' => 
			array (
				'single' => 
				array (
					'transformations_before' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
					'transformations_after' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
							0 => AgaviConfig::get('core.agavi_dir') . '/config/xsd/rbac_definitions.xsd',
						),
					),
				),
				'compilation' => 
				array (
					'transformations_before' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
					'transformations_after' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
				),
			),
		);
		
		$handler = new AgaviRbacDefinitionConfigHandler();
		// a new-style config handler
		// it does not parse the config itself; instead, it is given a complete and merged DOM document
		$doc = AgaviXmlConfigParser::run(AgaviConfig::get('core.config_dir') . '/tests/rbac_definitions.xml', AgaviConfig::get('core.environment'), null, $handlerInfo['transformations'], $handlerInfo['validations']);
		$handler->initialize(null, $handlerInfo['parameters']);
		$cfg = $this->includeCode($handler->execute($doc));
		
		$this->assertEquals($cfg, array(
			'administrator' => 
			array (
				'parent' => NULL,
				'permissions' => 
				array (
					'admin',
				),
			),
			'photographer' => 
			array (
				'parent' => 'member',
				'permissions' => 
				array (
					0 => 'photos.edit-own',
					1 => 'photos.add',
					2 => 'photos.lock',
				),
			),
			'photomoderator' => 
			array (
				'parent' => 'member',
				'permissions' => 
				array (
					0 => 'photos.edit',
					1 => 'photos.delete',
					2 => 'photos.unlock',
				),
			),
			'member' => 
			array (
				'parent' => 'guest',
				'permissions' => 
				array (
					0 => 'photos.comments.view',
					1 => 'photos.comments.add',
					2 => 'photos.rate',
					3 => 'lightbox',
					4 => 'tags.suggest',
				),
			),
			'guest' => 
			array (
				'parent' => NULL,
				'permissions' => 
				array (
					0 => 'photos.list',
					1 => 'photos.detail',
				),
			),
		));
	}
}

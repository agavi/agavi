<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class RbacdefinitionConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testHandler()
	{
		$handler = new AgaviRbacDefinitionConfigHandler();
		$cfg = $this->includeCode($handler->execute(AgaviConfig::get('core.config_dir') . '/tests/rbac_definitions.xml'));
		$this->assertEquals($cfg, array(
			'administrator' => 
			array (
				'parent' => NULL,
				'permissions' => 
				array (
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

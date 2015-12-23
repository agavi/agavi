<?php

class AgaviComposerLoaderShim {
	protected $triggerClasses = array(
		'AgaviConfig' => true,
		'Agavi' => true,
		'AgaviAutoloader' => true,
		'AgaviInflector' => true,
		'AgaviArrayPathDefinition' => true,
		'AgaviVirtualArrayPath' => true,
		'AgaviParameterHolder' => true,
		'AgaviConfigCache' => true,
		'AgaviException' => true,
		'AgaviAutoloadException' => true,
		'AgaviCacheException' => true,
		'AgaviConfigurationException' => true,
		'AgaviUnreadableException' => true,
		'AgaviParseException' => true,
		'AgaviToolkit' => true,
	);
	
	public function trigger($className) {
		if(!empty($this->triggerClasses[$className])) {
			require_once(__DIR__ . '/agavi.php');
		}
	}
}

spl_autoload_register(array(new AgaviComposerLoaderShim(), 'trigger'));

?>
<?php

class AgaviSampleAppBaseView extends AgaviView
{
	/*
		This is the base view all your application's views should extend.
		This way, you can easily inject new functionality into all of your views.
		
		One example would be to extend the initialize() method and assign commonly
		used objects such as the request as protected class members.
		
		Even if you don't need any of the above and this class remains empty, it is
		strongly recommended you keep it. There shall come the day where you are
		happy to have it this way ;)
		
		This default implementation throws an exception if execute() is called,
		which means that no execute*() method specific to the current output type
		was declared in your view, and no such method exists in this class either.
		
		It is of course highly recommended that you change the names of any default
		base classes to carry a prefix and have an overall meaningful naming scheme.
		You can enable the usage of the respective custom template files via
		build.properties settings. Also, keep in mind that you can define templates
		for specific modules in case you require this.
	*/
	
	const DEFAULT_SLOT_LAYOUT_NAME = 'slot';
	
	/**
	 * @var        AgaviRouting
	 */
	protected $ro;
	
	/**
	 * @var        AgaviRequest
	 */
	protected $rq;
	
	/**
	 * @var        AgaviTranslationManager
	 */
	protected $tm;
	
	/**
	 * @var        AgaviUser
	 */
	protected $us;
	
	public function initialize(AgaviExecutionContainer $container)
	{
		parent::initialize($container);
		
		$this->ro = $this->getContext()->getRouting();
		$this->rq = $this->getContext()->getRequest();
		$this->tm = $this->getContext()->getTranslationManager();
		$this->us = $this->getContext()->getUser();
	}
	
	public final function execute(AgaviRequestDataHolder $rd)
	{
		throw new AgaviViewException(sprintf(
			'The View "%1$s" does not implement an "execute%3$s()" method to serve '.
			'the Output Type "%2$s", and the base View "%4$s" does not implement an '.
			'"execute%3$s()" method to handle this situation.',
			get_class($this),
			$this->container->getOutputType()->getName(),
			ucfirst(strtolower($this->container->getOutputType()->getName())),
			get_class()
		));
	}
	
	public function executeHtml(AgaviRequestDataHolder $rd)
	{
		throw new AgaviViewException(sprintf(
			'The View "%1$s" does not implement an "execute%3$s()" method to serve '.
			'the Output Type "%2$s". It is recommended that you change the code of '.
			'the method "execute%3$s()" in the base View "%4$s" that is throwing '.
			'this exception to deal with this situation in a more appropriate '.
			'way, for example by forwarding to the default 404 error action, or by '.
			'showing some other meaningful error message to the user which explains '.
			'that the operation was unsuccessful beacuse the desired Output Type is '.
			'not implemented.',
			get_class($this),
			$this->container->getOutputType()->getName(),
			ucfirst(strtolower($this->container->getOutputType()->getName())),
			get_class()
		));
	}
	
	public function executeJson(AgaviRequestDataHolder $rd)
	{
		throw new AgaviViewException(sprintf(
			'The View "%1$s" does not implement an "execute%3$s()" method to serve '.
			'the Output Type "%2$s". It is recommended that you change the code of '.
			'the method "execute%3$s()" in the base View "%4$s" that is throwing '.
			'this exception to deal with this situation in a more appropriate '.
			'way, for example by forwarding to the default 404 error action, or by '.
			'showing some other meaningful error message to the user which explains '.
			'that the operation was unsuccessful beacuse the desired Output Type is '.
			'not implemented.',
			get_class($this),
			$this->container->getOutputType()->getName(),
			ucfirst(strtolower($this->container->getOutputType()->getName())),
			get_class()
		));
	}
	
	public function setupHtml(AgaviRequestDataHolder $rd, $layoutName = null)
	{
		if($layoutName === null && $this->getContainer()->getParameter('is_slot', false)) {
			$layoutName = self::DEFAULT_SLOT_LAYOUT_NAME;
		} else {
			// set a default title just to avoid warnings
			$this->setAttribute('_title', '');
		}
		
		$this->loadLayout($layoutName);
	}
}

?>
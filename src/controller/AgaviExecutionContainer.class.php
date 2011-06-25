<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A container used for each action execution that holds necessary information,
 * such as the output type, the response etc.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviExecutionContainer extends AgaviAttributeHolder
{
	/**
	 * @var        AgaviContext The context instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviValidationManager The validation manager instance.
	 */
	protected $validationManager = null;

	/**
	 * @var        string The request method for this container.
	 */
	protected $requestMethod = null;

	/**
	 * @var        AgaviRequestDataHolder A request data holder with request info.
	 */
	protected $requestData = null; // TODO: check if this can actually be protected 
	                               // or whether it should be private (would break actiontests though)

	/**
	 * @var        AgaviRequestDataHolder A pointer to the global request data.
	 */
	private $globalRequestData = null;

	/**
	 * @var        AgaviRequestDataHolder A request data holder with arguments.
	 */
	protected $arguments = null;

	/**
	 * @var        AgaviResponse A response instance holding the Action's output.
	 */
	protected $response = null;

	/**
	 * @var        AgaviOutputType The output type for this container.
	 */
	protected $outputType = null;

	/**
	 * @var        float The microtime at which this container was initialized.
	 */
	protected $microtime = null;

	/**
	 * @var        AgaviAction The Action instance that belongs to this container.
	 */
	protected $actionInstance = null;

	/**
	 * @var        AgaviView The View instance that belongs to this container.
	 */
	protected $viewInstance = null;

	/**
	 * @var        string The name of the Action's Module.
	 */
	protected $moduleName = null;

	/**
	 * @var        string The name of the Action.
	 */
	protected $actionName = null;

	/**
	 * @var        string Name of the module of the View returned by the Action.
	 */
	protected $viewModuleName = null;

	/**
	 * @var        string The name of the View returned by the Action.
	 */
	protected $viewName = null;

	/**
	 * @var        AgaviExecutionContainer The next container to execute.
	 */
	protected $next = null;

	/**
	 * Action names may contain any valid PHP token, as well as dots and slashes
	 * (for sub-actions).
	 */
	const SANE_ACTION_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\/.]*/';
	
	/**
	 * View names may contain any valid PHP token, as well as dots and slashes
	 * (for sub-actions).
	 */
	const SANE_VIEW_NAME   = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\/.]*/';
	
	/**
	 * Only valid PHP tokens are allowed in module names.
	 */
	const SANE_MODULE_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context instead of the instance, and the name of
	 * the output type instead of the instance. Both will be restored by __wakeup
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$this->contextName = $this->context->getName();
		if(!empty($this->outputType)) {
			$this->outputTypeName = $this->outputType->getName();	
		}
		$arr = get_object_vars($this);
		unset($arr['context'], $arr['outputType'], $arr['requestData'], $arr['globalRequestData']);
		return array_keys($arr);
	}

	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context and output type instances based on their names set
	 * by __sleep.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __wakeup()
	{
		$this->context = AgaviContext::getInstance($this->contextName);
		
		if(!empty($this->outputTypeName)) {
			$this->outputType = $this->context->getController()->getOutputType($this->outputTypeName);
		}
		
		try {
			$this->globalRequestData = $this->context->getRequest()->getRequestData();
		} catch(AgaviException $e) {
			$this->globalRequestData = new AgaviRequestDataHolder();
		}
		unset($this->contextName, $this->outputTypeName);
	}

	/**
	 * Initialize the container. This will create a response instance.
	 *
	 * @param      AgaviContext The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->microtime = microtime(true);

		$this->context = $context;

		$this->parameters = $parameters;

		$this->response = $this->context->createInstanceFor('response');
	}

	/**
	 * Creates a new container instance with the same output type and request
	 * method as this one.
	 *
	 * @param      string                 The name of the module.
	 * @param      string                 The name of the action.
	 * @param      AgaviRequestDataHolder A RequestDataHolder with additional
	 *                                    request arguments.
	 * @param      string                 Optional name of an initial output type
	 *                                    to set.
	 * @param      string                 Optional name of the request method to
	 *                                    be used in this container.
	 *
	 * @return     AgaviExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createExecutionContainer($moduleName = null, $actionName = null, AgaviRequestDataHolder $arguments = null, $outputType = null, $requestMethod = null)
	{
		if($outputType === null) {
			$outputType = $this->getOutputType()->getName();
		}
		if($requestMethod === null) {
			$requestMethod = $this->getRequestMethod();
		}
		
		$container = $this->context->getController()->createExecutionContainer($moduleName, $actionName, $arguments, $outputType, $requestMethod);
		
		// copy over parameters (could be is_slot, is_forward etc)
		$container->setParameters($this->getParameters());
		
		return $container;
	}

	/**
	 * Start execution.
	 *
	 * This will create an instance of the action and merge in request parameters.
	 *
	 * This method returns a response. It is not necessarily the same response as
	 * the one of this container, but instead the one that contains the actual
	 * content that should be used for output etc, since the container's own
	 * response might be empty or invalid due to a "next" container that has been
	 * set and executed.
	 *
	 * @return     AgaviResponse The "real" response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute()
	{
		$controller = $this->context->getController();

		$controller->countExecution();

		$moduleName = $this->getModuleName();
		$actionName = $this->getActionName();
		
		try {
			// TODO: cleanup and merge with createActionInstance once Exceptions have been cleaned up and specced properly so that the two error conditions can be told apart
			if(false === $controller->checkActionFile($moduleName, $actionName)) {
				$this->setNext($this->createSystemActionForwardContainer('error_404'));
				return $this->proceed();
			}
			
			$this->actionInstance = $controller->createActionInstance($moduleName, $actionName);
		} catch(AgaviDisabledModuleException $e) {
			$this->setNext($this->createSystemActionForwardContainer('module_disabled'));
			return $this->proceed();
		}
		
 
		// initialize the action
		$this->actionInstance->initialize($this);

		// copy and merge request data as required
		$this->initRequestData();
		
		if($this->actionInstance->isSimple()) {
			// run the execution filter, without a proper chain
			$controller->getFilter('execution')->execute(new AgaviFilterChain(), $this);
		} else {

			// create a new filter chain
			$filterChain = $this->context->createInstanceFor('filter_chain');

			if(AgaviConfig::get('core.available', false)) {
				// the application is available so we'll register
				// globally defined and module-specific action filters, otherwise skip them

				// does this action require security?
				if(AgaviConfig::get('core.use_security', false)) {
					// register security filter
					$filterChain->register($controller->getFilter('security'));
				}

				// load filters
				$controller->loadFilters($filterChain, 'action');
				$controller->loadFilters($filterChain, 'action', $moduleName);
			}

			// register the execution filter
			$filterChain->register($controller->getFilter('execution'));

			// process the filter chain
			$filterChain->execute($this);
		}
		
		return $this->proceed();
	}
	
	/**
	 * Copies and merges the global request data.
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.1.0
	 */
	protected function initRequestData()
	{
		if($this->actionInstance->isSimple()) {
			if($this->arguments !== null) {
				// clone it so mutating it has no effect on the "outside world"
				$this->requestData = clone $this->arguments;
			} else {
				$rdhc = $this->getContext()->getRequest()->getParameter('request_data_holder_class');
				$this->requestData = new $rdhc();
			}
		} else {
			// mmmh I smell awesomeness... clone the RD JIT, yay, that's the spirit
			$this->requestData = clone $this->globalRequestData;

			if($this->arguments !== null) {
				$this->requestData->merge($this->arguments);
			}
		}
	}
	
	/**
	 * Create a system forward container
	 *
	 * Calling this method will set the attributes:
	 *  - requested_module
	 *  - requested_action
	 *  - (optional) exception
	 * in the appropriate namespace on the created container as well as the global
	 * request (for legacy reasons)
	 *
	 *
	 * @param      string          The type of forward to create (error_404, 
	 *                             module_disabled, secure, login, unavailable).
	 * @param      AgaviException  Optional exception thrown by the controller
	 *                             while resolving the module/action.
	 *
	 * @return     AgaviExecutionContainer The forward container.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function createSystemActionForwardContainer($type, AgaviException $e = null)
	{
		if(!in_array($type, array('error_404', 'module_disabled', 'secure', 'login', 'unavailable'))) {
			throw new AgaviException(sprintf('Unknown system forward type "%1$s"', $type));
		}
		
		// track the requested module so we have access to the data in the error 404 page
		$forwardInfoData = array(
			'requested_module' => $this->getModuleName(),
			'requested_action' => $this->getActionName(),
			'exception'        => $e,
		);
		$forwardInfoNamespace = 'org.agavi.controller.forwards.' . $type;
		
		$moduleName = AgaviConfig::get('actions.' . $type . '_module');
		$actionName = AgaviConfig::get('actions.' . $type . '_action');
		
		if(false === $this->context->getController()->checkActionFile($moduleName, $actionName)) {
			// cannot find unavailable module/action
			$error = 'Invalid configuration settings: actions.%3$s_module "%1$s", actions.%3$s_action "%2$s"';
			$error = sprintf($error, $moduleName, $actionName, $type);
			
			throw new AgaviConfigurationException($error);
		}
		
		$forwardContainer = $this->createExecutionContainer($moduleName, $actionName);
		
		$forwardContainer->setAttributes($forwardInfoData, $forwardInfoNamespace);
		// legacy
		$this->context->getRequest()->setAttributes($forwardInfoData, $forwardInfoNamespace);
		
		return $forwardContainer;
	}
	
	/**
	 * Proceed to the "next" container by running it and returning its response,
	 * or return our response if there is no "next" container.
	 *
	 * @return     AgaviResponse The "real" response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	protected function proceed()
	{
		if($this->next !== null) {
			return $this->next->execute();
		} else {
			return $this->getResponse();
		}
	}

	/**
	 * Get the Context.
	 *
	 * @return     AgaviContext The Context.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the ValidationManager
	 *
	 * @return     AgaviValidationManager The container's ValidationManager
	 *                                    implementation instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getValidationManager()
	{
		if($this->validationManager === null) {
			$this->validationManager = $this->context->createInstanceFor('validation_manager');
		}
		return $this->validationManager;
	}
	
	
	/**
	 * Execute the Action.
	 *
	 * @return     mixed The processed View information returned by the Action.
	 *
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function runAction()
	{
		$context = $this->getContext();
		$controller = $context->getController();
		
		// create a new filter chain
		$filterChain = $context->createInstanceFor('filter_chain');
		
		// register necessary filters
		if(!$this->getActionInstance()->isSimple()) {
			$filter = $controller->getFilter('validation');
			$filterChain->register($filter);
			$filter = $controller->getFilter('authorization');
			$filterChain->register($filter);
		}
		
		$filter = $controller->getFilter('action_execution');
		$filterChain->register($filter);
		
		// rock and roll
		$filterChain->execute($this);
		
		return array($this->getViewModuleName(), $this->getViewName());
	}
	
	/**
	 * Resolve the name of the given View.
	 *
	 * @param      mixed The View name (array of Module and View or View only).
	 * @param      mixed The name of the Action to resolve for.
	 * @param      mixed The name of the View to resolve for.
	 *
	 * @return     array An array containing Module name and name for the View.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function resolveViewName($viewName, $actionName, $moduleName)
	{
		if(is_array($viewName) && count($viewName) >= 2) {
			// we're going to use an entirely different action for this view
			$viewModule = $viewName[0];
			$viewName   = $viewName[1];
		} elseif($viewName !== AgaviView::NONE) {
			// use a view related to this action
			$viewName = AgaviToolkit::evaluateModuleDirective(
				$moduleName,
				'agavi.view.name',
				array(
					'actionName' => $actionName,
					'viewName' => $viewName,
				)
			);
			$viewModule = $moduleName;
		} else {
			$viewName = AgaviView::NONE;
			$viewModule = AgaviView::NONE;
		}

		return array($viewModule, $viewName === AgaviView::NONE ? AgaviView::NONE : AgaviToolkit::canonicalName($viewName));
	}
	
	/**
	 * Performs validation for this execution container.
	 * 
	 * @return     bool true if the data validated successfully, false otherwise.
	 * 
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function performValidation()
	{
		$validationManager = $this->getValidationManager();

		// get the current action instance
		$actionInstance = $this->getActionInstance();
		// get the (already formatted) request method
		$method = $this->getRequestMethod();

		$requestData = $this->getRequestData();
		
		// set default validated status
		$validated = true;

		$this->registerValidators();

		// process validators
		$validated = $validationManager->execute($requestData);

		$validateMethod = 'validate' . $method;
		if(!is_callable(array($actionInstance, $validateMethod))) {
			$validateMethod = 'validate';
		}

		// process manual validation
		$manuallyValidated = $actionInstance->$validateMethod($requestData);
		if($validated && !$manuallyValidated) {
			// validation manager did not yield errors, but the manual validation indicated a failure
			// set the appropriate status code on the validation result
			$report = $validationManager->getReport();
			if($report->getResult() < AgaviValidator::ERROR) {
				$report->setResult(AgaviValidator::ERROR);
			}
		}
		return $manuallyValidated && $validated;
	}

	/**
	 * Register validators for this execution container.
	 * 
	 * @author     David Zülke <david.zuelke@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function registerValidators()
	{
		$validationManager = $this->getValidationManager();

		// get the current action instance
		$actionInstance = $this->getActionInstance();
		
		// get the current action information
		$moduleName = $this->getModuleName();
		$actionName = $this->getActionName();
		
		// get the (already formatted) request method
		$method = $this->getRequestMethod();

		// get the current action validation configuration
		$validationConfig = AgaviToolkit::evaluateModuleDirective(
			$moduleName,
			'agavi.validate.path',
			array(
				'moduleName' => $moduleName,
				'actionName' => $actionName,
			)
		);
		if(is_readable($validationConfig)) {
			// load validation configuration
			// do NOT use require_once
			require(AgaviConfigCache::checkConfig($validationConfig, $this->context->getName()));
		}

		// manually load validators
		$registerValidatorsMethod = 'register' . $method . 'Validators';
		if(!is_callable(array($actionInstance, $registerValidatorsMethod))) {
			$registerValidatorsMethod = 'registerValidators';
		}
		$actionInstance->$registerValidatorsMethod();
	}
	
	/**
	 * Retrieve this container's request method name.
	 *
	 * @return     string The request method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	/**
	 * Set this container's request method name.
	 *
	 * @param      string The request method name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function setRequestMethod($requestMethod)
	{
		$this->requestMethod = $requestMethod;
	}

	/**
	 * Retrieve this container's request data holder instance.
	 *
	 * @return     AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getRequestData()
	{
		return $this->requestData;
	}

	/**
	 * Set this container's global request data holder reference.
	 *
	 * @param      AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function setRequestData(AgaviRequestDataHolder $rd)
	{
		$this->globalRequestData = $rd;
	}

	/**
	 * Get this container's request data holder instance for additional arguments.
	 *
	 * @return     AgaviRequestDataHolder The additional arguments.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Set this container's request data holder instance for additional arguments.
	 *
	 * @return     AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setArguments(AgaviRequestDataHolder $arguments)
	{
		$this->arguments = $arguments;
	}

	/**
	 * Retrieve this container's response instance.
	 *
	 * @return     AgaviResponse The Response instance for this action.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Set a new response.
	 *
	 * @param      AgaviResponse A new Response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setResponse(AgaviResponse $response)
	{
		$this->response = $response;
		// do not set the output type on the response here!
	}

	/**
	 * Retrieve the output type of this container.
	 *
	 * @return     AgaviOutputType The output type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}

	/**
	 * Set a different output type for this container.
	 *
	 * @param      AgaviOutputType An output type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType(AgaviOutputType $outputType)
	{
		$this->outputType = $outputType;
		if($this->response) {
			$this->response->setOutputType($outputType);
		}
	}

	/**
	 * Retrieve this container's microtime.
	 *
	 * @return     string A string representing the microtime this container was
	 *                    initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getMicrotime()
	{
		return $this->microtime;
	}

	/**
	 * Retrieve this container's action instance.
	 *
	 * @return     AgaviAction An action implementation instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionInstance()
	{
		return $this->actionInstance;
	}

	/**
	 * Retrieve this container's view instance.
	 *
	 * @return     AgaviView A view implementation instance.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.0
	 */
	public function getViewInstance()
	{
		return $this->viewInstance;
	}

	/**
	 * Set this container's view instance.
	 *
	 * @param      AgaviView A view implementation instance.
	 *
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @since      0.11.0
	 */
	public function setViewInstance($viewInstance)
	{
		return $this->viewInstance = $viewInstance;
	}

	/**
	 * Retrieve this container's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Retrieve this container's action name.
	 *
	 * @return     string An action name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionName()
	{
		return $this->actionName;
	}

	/**
	 * Retrieve this container's view module name. This is the name of the module of
	 * the View returned by the Action.
	 *
	 * @return     string A view module name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewModuleName()
	{
		return $this->viewModuleName;
	}

	/**
	 * Retrieve this container's view name.
	 *
	 * @return     string A view name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewName()
	{
		return $this->viewName;
	}

	/**
	 * Set the module name for this container.
	 *
	 * @param      string A module name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setModuleName($moduleName)
	{
		if(null === $moduleName) {
			$this->moduleName = null;
		} elseif(preg_match(self::SANE_MODULE_NAME, $moduleName)) {
			$this->moduleName = $moduleName;
		} else {
			throw new AgaviException(sprintf('Invalid module name "%1$s"', $moduleName));
		}
	}

	/**
	 * Set the action name for this container.
	 *
	 * @param      string An action name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setActionName($actionName)
	{
		if(null === $actionName) {
			$this->actionName = null;
		} elseif(preg_match(self::SANE_ACTION_NAME, $actionName)) {
			$actionName = AgaviToolkit::canonicalName($actionName);
			$this->actionName = $actionName;
		} else {
			throw new AgaviException(sprintf('Invalid action name "%1$s"', $actionName));
		}
	}

	/**
	 * Set the view module name for this container.
	 *
	 * @param      string A view module name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewModuleName($viewModuleName)
	{
		if(null === $viewModuleName) {
			$this->viewModuleName = null;
		} elseif(preg_match(self::SANE_MODULE_NAME, $viewModuleName)) {
			$this->viewModuleName = $viewModuleName;
		} else {
			throw new AgaviException(sprintf('Invalid view module name "%1$s"', $viewModuleName));
		}
	}

	/**
	 * Set the module name for this container.
	 *
	 * @param      string A view name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewName($viewName)
	{
		if(null === $viewName) {
			$this->viewName = null;
		} elseif(preg_match(self::SANE_VIEW_NAME, $viewName)) {
			$viewName = AgaviToolkit::canonicalName($viewName);
			$this->viewName = $viewName;
		} else {
			throw new AgaviException(sprintf('Invalid view name "%1$s"', $viewName));
		}
	}

	 /**
	 * Check if a "next" container has been set.
	 *
	 * @return     bool True, if a container for eventual execution has been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasNext()
	{
		return $this->next !== null;
	}

	/**
	 * Get the "next" container.
	 *
	 * @return     AgaviExecutionContainer The "next" container, of null if unset.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set the container that should be executed once this one finished running.
	 *
	 * @param      AgaviExecutionContainer An execution container instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setNext(AgaviExecutionContainer $container)
	{
		$this->next = $container;
	}

	/**
	 * Remove a possibly set "next" container.
	 *
	 * @return     AgaviExecutionContainer The removed "next" container, or null
	 *                                     if none had been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearNext()
	{
		$retval = $this->next;
		$this->next = null;
		return $retval;
	}
}

?>
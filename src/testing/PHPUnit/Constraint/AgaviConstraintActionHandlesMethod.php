<?php 
/**
 * Constraint that checks if an Action handles an expected request method.
 * 
 * The Action instance is passed to the constructor.
 *
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviConstraintActionHandlesMethod extends AgaviBaseConstraintBecausePhpunitSucksAtBackwardsCompatibility
{
	/**
	 * @var        AgaviAction The Action instance.
	 */
	protected $actionInstance;
	
	/**
	 * @var        bool Whether generic 'execute' methods should be accepted.
	 */
	protected $acceptGeneric;
	
	/**
	 * Class constructor.
	 * 
	 * @param      AgaviAction Instance of the Action to test.
	 * @param      bool        Whether generic execute methods should be accepted.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviAction $actionInstance, $acceptGeneric = true)
	{
		$this->actionInstance = $actionInstance;
		$this->acceptGeneric = $acceptGeneric;
	}
	
	/**
	 * Evaluates the constraint for parameter $other. Returns TRUE if the
	 * constraint is met, FALSE otherwise.
	 *
	 * @param      mixed Value or object to evaluate.
	 *
	 * @return     bool The result of the evaluation.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.7
	 */
	public function matches($other)
	{
		$executeMethod = 'execute' . $other;
		if(is_callable(array($this->actionInstance, $executeMethod)) || ($this->acceptGeneric && is_callable(array($this->actionInstance, 'execute')))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns a string representation of the constraint.
	 *
	 * @return     string The string representation.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function toString()
	{
		return sprintf(
			'%1$s handles method',
			get_class($this->actionInstance)
		);
	}
	
	/**
	 * Returns a custom error description.
	 * 
	 * @param      mixed  Value or object to evaluate.
	 * @param      string The original description.
	 * @param      bool   true if the constraint was negated.
	 * 
	 * @return     string The error description.
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function customFailureDescription($other, $description, $not)
	{
		if($not) {
			return sprintf(
				'Failed asserting that %1$s does not handle method "%2$s".',
				get_class($this->actionInstance),
				$other
			);
		} else {
			return sprintf(
				'Failed asserting that %1$s handles method "%2$s".',
				get_class($this->actionInstance),
				$other
			);
		}
	}
}
?>
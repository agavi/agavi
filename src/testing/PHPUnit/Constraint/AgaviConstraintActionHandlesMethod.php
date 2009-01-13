<?php 
/**
 * Constraint that checks if an action handles an expected method
 * 
 * The actionInstance is passed in the constructor.
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
class AgaviConstraintActionHandlesMethod extends PHPUnit_Framework_Constraint
{
	
	/**
	 * @var        AgaviAction the action instance
	 */
	protected $actionInstance;
	
	/**
	 * @var        boolean true if 'execute' should be accepted
	 */
	protected $acceptGeneric;
	
	/**
	 * contstructor
	 * 
	 * @param      AgaviAction the action to test
	 * @param      boolean     true if 'execute' should be accepted
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
	 * @param mixed $other Value or object to evaluate.
	 * @return bool
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function evaluate($other)
	{
		$executeMethod = 'execute' . $other;
		if(method_exists($this->actionInstance, $executeMethod) || ($this->acceptGeneric && method_exists($this->actionInstance, 'execute'))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns a string representation of the constraint.
	 *
	 * @return string
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
	 * returns a custom error description
	 * 
	 * @param      mixed  Value or object to evaluate.
	 * @param      string the original description
	 * @param      bool   true if the constraint was negated
	 * 
	 * @return     string the error description
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function customFailureDescription($other, $description, $not)
	{
		if(!$not) {
			return sprintf(
				'Failed asserting that %1$s handles method "%2$s".', get_class($this->actionInstance), $other
				);
		} else {
			return sprintf(
				'Failed asserting that %1$s does not handle method "%2$s".', get_class($this->actionInstance), $other
			);
		}
	}
}
?>
<?php 
/**
 * Constraint that checks if an view handles an expected outputType
 * 
 * The viewInstance is passed in the constructor.
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
class AgaviConstraintViewHandlesOutputType extends PHPUnit_Framework_Constraint
{
	
	/**
	 * @var        AgaviAction the action instance
	 */
	protected $viewInstance;
	
	/**
	 * @var        boolean true if 'execute' should be accepted
	 */
	protected $acceptGeneric;
	
	/**
	 * constructor
	 * 
	 * @param      AgaviAction the action to test
	 * @param      boolean     true if 'execute' should be accepted
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviView $viewInstance, $acceptGeneric = false)
	{
		$this->viewInstance = $viewInstance;
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
		if(method_exists($this->viewInstance, $executeMethod) || ($this->acceptGeneric && method_exists($this->viewInstance, 'execute'))) {
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
			'%1$s handles output type',
		
			get_class($this->viewInstance)
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
				'Failed asserting that %1$s handles output type "%2$s".', get_class($this->viewInstance), $other
				);
		} else {
			return sprintf(
				'Failed asserting that %1$s does not handle output type "%2$s".', get_class($this->viewInstance), $other
			);
		}
	}
}
?>
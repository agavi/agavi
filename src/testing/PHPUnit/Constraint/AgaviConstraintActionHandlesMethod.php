<?php 

class AgaviConstraintActionHandlesMethod extends PHPUnit_Framework_Constraint
{
	protected $actionInstance;
	protected $acceptGeneric;
	
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
	 */
	public function toString()
	{
	    return sprintf(
	      '%1$s handles method',
	
	      get_class($this->actionInstance)
	    );
	}
    
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
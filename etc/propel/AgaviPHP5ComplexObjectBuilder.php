<?php

include_once('propel/engine/builder/om/php5/PHP5ComplexObjectBuilder.php');

class AgaviPHP5ComplexObjectBuilder extends PHP5ComplexObjectBuilder
{
	public function buildObjectInstanceCreationCode($obj, $cls)
	{
		return parent::buildObjectInstanceCreationCode($obj, $cls) . "$obj->initialize(\$this->context);\n";
	}

	protected function addClassOpen(&$script)
	{
		$table = $this->getTable();
		$tableName = $table->getName();
		$tableDesc = $table->getDescription();
		$interface = $this->getInterface();

		$script .= '
abstract class ' . $this->getClassname() . ' extends ' . ClassTools::classname($this->getBaseClass()) . ' implements ';
		$interface = ClassTools::getInterface($table);
		if($interface) {
			$script .= ClassTools::classname($interface) . ', ';
		}
		$script .= 'AgaviIModel {
	
	protected $context = null;

	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
	}
	
	public function getContext()
	{
		return $this->context;
	}
';
	}
}

?>
<?php

include_once('propel/engine/builder/om/php5/PHP5ComplexPeerBuilder.php');

class AgaviPHP5ComplexPeerBuilder extends PHP5ComplexPeerBuilder
{
	public function buildObjectInstanceCreationCode($obj, $cls)
	{
		return parent::buildObjectInstanceCreationCode($obj, $cls) . "$obj" . "->initialize(\$this->context);\n";
	}
	
	protected function addClassOpen(&$script) {

		$tableName = $this->getTable()->getName();
		$tableDesc = $this->getTable()->getDescription();

		$script .= '
abstract class '.$this->getClassname().' implements AgaviISingletonModel {
	
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
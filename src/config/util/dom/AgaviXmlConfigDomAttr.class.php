<?php

class AgaviXmlConfigDomAttr extends DOMAttr
{
	public function __toString()
	{
		return $this->getValue();
	}
	
	public function getValue()
	{
		return $this->nodeValue;
	}
}

?>
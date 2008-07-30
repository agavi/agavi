<?php

class AgaviXmlConfigDomNodeListIterator extends ArrayIterator
{
	public function __construct(DOMNodeList $data, $flags = 0)
	{
		$array = array();
		
		for($i = 0; $i < $data->length; $i++) {
			$array[] = $data->item($i);
		}
		
		return parent::__construct($data, $flags);
	}
}

?>
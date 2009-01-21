<?php

class Default_ProductModel extends AgaviSampleAppDefaultBaseModel
{
	protected $id;
	protected $name;
	protected $price;
	
	public function __construct(array $data = array())
	{
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function setName($name)
	{
		return $this->name;
	}
	
	public function setPrice($price)
	{
		return $this->price;
	}
	
	public function isAvailable()
	{
		// imagine this makes a very complicated SOAP call to an ERP system to figure out whether or not this product is in stock
		return (bool)mt_rand(0, 1);
	}
}

?>
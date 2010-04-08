<?php

class ProductModel extends AgaviSampleAppBaseModel
{
	protected $id;
	protected $name;
	protected $price;
	
	protected static $fields = array(
		'id',
		'name',
		'price'
	);
	
	public function __construct(array $data = array())
	{
		$this->fromArray($data);
	}
	
	public function fromArray(array $data)
	{
		foreach(self::$fields as $field) {
			if(isset($data[$field])) {
				$this->$field = $data[$field];
			}
		}
	}
	
	public function toArray()
	{
		$retval = array();
		
		foreach(self::$fields as $field) {
			$retval[$field] = $this->$field;
		}
		
		return $retval;
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
		$this->name = $name;
	}
	
	public function setPrice($price)
	{
		$this->price = $price;
	}
	
	public function isAvailable()
	{
		// imagine this makes a very complicated SOAP call to an ERP system to figure out whether or not this product is in stock
		return (bool)mt_rand(0, 1);
	}
}

?>
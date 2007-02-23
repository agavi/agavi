<?php


class DateValidatorWrapper extends AgaviDateValidator
{
	protected $data;

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getData($paramname)
	{
		return $this->data;
	}

	public function validate()
	{
		return parent::validate();
	}

}

class DateValidatorTest extends AgaviTestCase
{
}
?>
<?php
abstract class AgaviWebRequestDataHolderTest extends AgaviUnitTestCase
{
	protected function getDefaultDataHolder()
	{
		return new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => $this->getDefaultNestedInputData(),
				AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $this->getDefaultNestedInputData(),
				AgaviWebRequestDataHolder::SOURCE_HEADERS => $this->getDefaultHeaders(),
			)
		);
	}
	
	protected function getDefaultNestedInputData()
	{
		return array(
			'flat'   => 'flatvalue',
			'nested' => array(
				'level1' => 'level1 value',
				'level2' => array(
					'level3' => 'level3 value',
					'nullkey' => null,
					'emptystring' => '',
				),
			),
			'nullvalue'   => null,
			'falsevalue'  => false,
			'emptystring' => '',
			'zerovalue'   => 0,
			'objectvalue' => new stdClass(),
		);
	}
	
	/**
	 * returns information on the default nested data set
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists (returns 'true' on hasParameter/Cookie/...)
	 *   - key considered empty (returns 'true' on isParameter/Cookie/...ValueEmpty)
	 */
	public function parameterData()
	{
		
	}
	
	/**
	 * returns information on what to expect when reading the default nested data set 
	 * 
	 * duplicates the parameter information to have a test with 
	 * and without a default value
	 * 
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists (returns 'true' on hasParameter/Cookie/...)
	 *   - key considered empty (returns 'true' on isParameter/Cookie/...ValueEmpty)
	 *   - whether the dataset expected the default value
	 */
	public function getParameterReadInformation()
	{
		$readInformation = array();
		
		foreach($this->parameterData() as $key => $parameterInfo) {
			$readInformation[$key] = $parameterInfo;
			$readInformation[$key][4] = false;
			$readInformation[$key.',default'] = $parameterInfo;
			$readInformation[$key.',default'][4] = true;
			if(false == $parameterInfo[2])
			{
				$readInformation[$key.',default'][1] = 'default';
			}
		}
		
		return $readInformation;
	}
	
	public function getFlatDefaultParameterNames()
	{
		return array(
			'flat', 
			'nested[level1]', 
			'nested[level2][level3]', 
			'nested[level2][nullkey]',
			'nested[level2][emptystring]',
			'nullvalue', 
			'falsevalue', 
			'emptystring',
			'zerovalue',
			'objectvalue',
		);
	}
	
	public function getDefaultParameterNames()
	{
		return array('flat', 'nested', 'nullvalue', 'falsevalue', 'emptystring', 'zerovalue', 'objectvalue',);
	}
	
	public function getDefaultHeaders()
	{
		return array(
			'FLAT_HEADER' => 'flatvalue',
			'NESTED_HEADER' => array(          // array headers don't exist, but we need to check 
				'NESTED_KEY' => 'nestedvalue', // that virtual array access does indeed not work
			),
			'NULL_VALUE' => null,
			'ZERO_VALUE' => 0,
			'FALSE_VALUE' => false,
			'EMPTY_STRING' => '',
			'CONTAINS[BRACKETS]' => 'contains_brackets',
		);
	}
	
	/**
	 * returns information on the default header data set
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists
	 *   - key considered empty
	 */
	public function getDefaultHeaderInformation()
	{
		return array(
			'FLAT_HEADER,caps,underscore' => array(
				'FLAT_HEADER',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,caps,hyphen' => array(
				'FLAT-HEADER',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,non-caps,underscore' => array(
				'flat_header',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,non-caps,hyphen' => array(
				'flat-header',
				'flatvalue',
				true,
				false,
			),
			'MISSING_HEADER' => array(
				'MISSING_HEADER',
				null,
				false,
				true,
			),
			'NESTED_HEADER' => array(
				'NESTED_HEADER',
				array(
					'NESTED_KEY' => 'nestedvalue',
				),
				true,
				false,
			),
			'NESTED_HEADER-1' => array(
				'NESTED_HEADER[NESTED_KEY]',
				null,
				false,
				true,
			),
			'NULL_VALUE' => array(
				'NULL_VALUE',
				null,
				true,
				true,
			),
			'ZERO_VALUE' => array(
				'ZERO_VALUE',
				0,
				true,
				false,
			),
			'FALSE_VALUE' => array(
				'FALSE_VALUE',
				false,
				true,
				false,
			),
			'EMPTY_STRING' => array(
				'EMPTY_STRING',
				'',
				true,
				false,
			),
			'CONTAINS[BRACKETS]' => array(
				'CONTAINS[BRACKETS]',
				'contains_brackets',
				true,
				false,
			),
		);
	}
	
	public function getHeaderReadInformation()
	{
		$readInformation = array();
		
		foreach($this->getDefaultHeaderInformation() as $key => $info) {
			$readInformation[$key] = $info;
			$readInformation[$key][4] = false;
			$readInformation[$key.',default'] = $info;
			$readInformation[$key.',default'][4] = true;
			if(false === $info[2]) {
				$readInformation[$key.',default'][1] = 'default';
			}
		}
		
		return $readInformation;
	}

}

?>
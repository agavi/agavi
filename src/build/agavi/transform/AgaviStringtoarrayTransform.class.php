<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * Transforms an input string to an array.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviStringtoarrayTransform extends AgaviTransform
{
	protected $delimiter = ' ';
	
	/**
	 * Sets the delimiter.
	 *
	 * @param      string The delimiter for parsing the input string.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}
	
	/**
	 * Transforms an input string to an array.
	 *
	 * @return     array The transformed array.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function transform()
	{
		$input = $this->getInput();
		
		if($input === null) {
			return array();
		}
		
		$delimiter = preg_quote($this->delimiter, '#');
		$pattern = sprintf('#(?:(?P<unquoted>[^"\'%s].+)|\'(?P<single_quoted>(?:\\\\\'|[^\'])+)\'|"(?P<double_quoted>(?:\\\\"|[^"])+)")(?=[%s]|$)#U',
			$delimiter, $delimiter);
		
		$matches = array();
		preg_match_all($pattern, $input, $matches, PREG_SET_ORDER);
		
		$result = array();
		foreach($matches as $match) {
			/* This has everything to do with the order of the regular expression.
			 * Watch it. */
			if(!empty($match['double_quoted'])) {
				$result[] = str_replace('\\"', '"', $match['double_quoted']);
			} elseif(!empty($match['single_quoted'])) {
				$result[] = str_replace('\\\'', '\'', $match['single_quoted']);
			} else {
				$result[] = $match['unquoted'];
			}
		}
		
		return $result;
	}
}

?>
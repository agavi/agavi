<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * The decimal formatter will format numbers according to a given format
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDecimalFormatter
{
	/**
	 * @var        string The format string which will be given to sprintf
	 */
	protected $formatString = '';

	/**
	 * @var        int The minimum number of integrals displayed (will be padded
	 *                 with 0 on the left)
	 */
	protected $minShowedIntegrals = 0;

	/**
	 * @var        int The minimum number of fractionals displayed (will be 
	 *                 padded with 0 on the right)
	 */
	protected $minShowedFractionals = 0;

	/**
	 * @var        int The maximum number of fractionals displayed 
	 *                 (-1 means all get displayed)
	 */
	protected $maxShowedFractionals = 0;

	/**
	 * @var        bool Whether the format string has the location of the minus 
	 *                  defined
	 */
	protected $hasMinus = false;


	/**
	 * @var        bool Whether the format string has the location of the 
	 *                  currency sign defined
	 */
	protected $hasCurrency = false;

	/**
	 * @var        array An array containing the distances for the grouping 
	 *                   operators which will be applied to the number
	 */
	protected $groupingDistances = array();

	/**
	 * @var        string The grouping(thousands) separator
	 */
	protected $groupingSeparator = ',';

	/**
	 * @var        string The decimal separator
	 */
	protected $decimalSeparator = '.';

	/**
	 * @var        int The rounding mode
	 */
	protected $roundingMode = AgaviDecimalFormatter::ROUND_SCIENTIFIC;


	const ROUND_NONE = 0;
	const ROUND_SCIENTIFIC = 1;
	const ROUND_FINANCIAL = 2;
	const ROUND_FLOOR = 3;
	const ROUND_CEIL = 4;

	const IN_PREFIX = 1;
	const IN_NUMBER = 2;
	const IN_POSTFIX = 3;

	public function __construct($format = null)
	{
		if($format) {
			$this->parseFormatString($format);
		}
	}

	public function parseFormatString($format)
	{

		$numberChars = array('0', '#', '.', ',');

		$formatStr = '';

		// an array containing the distances between the grouping operators (and up to the decimals) from left to right
		$groupingDistances = array();
		$currentGroupingDistance = 0;

		$hasMinus = false;
		$hasCurrency = false;
		$minShowedIntegrals = 0;
		$minShowedFractionals = 0;
		$maxShowedFractionals = 0;
		$skippedFractionals = 0;
		$numberState = 'inInteger';

		$inQuote = false;
		$quoteStr = '';
		$state = self::IN_PREFIX;
		$len = strlen($format);

		for($i = 0; $i < $len; ++$i) {
			$c = $format[$i];
			$cNext = (($i + 1) < $len) ? $format[$i + 1] : 0;

			switch($state) {
				case self::IN_POSTFIX:
				case self::IN_PREFIX: {
					if($state == self::IN_PREFIX && in_array($c, $numberChars)) {
						--$i;
						$state = self::IN_NUMBER;
					} elseif($inQuote) {
						// quote closed
						if($c == '\'') {
							// when the quoted string was empty we need to output a '
							if(strlen($quoteStr) == 0) {
								$quoteStr = '\'';
							}
							$formatStr .= $quoteStr;
							$inQuote = false;
						} else {
							// quote % for sprintf usage
							if($c == '%') {
								$c = '%' . $c;
							}
							$quoteStr .= $c;
						}
					} else {
						if($c == '\'') {
							$quoteStr = '';
							$inQuote = true;
						} elseif($c == '-') {
							$hasMinus = true;
							$formatStr .= '%2$s';
						} elseif(/*$c == '¤'*/ ord($c) == 194 && ord($cNext) == 164) {
							++$i;
							$hasCurrency = true;
							$formatStr .= '%3$s';
						} else {
							// quote % for sprintf usage
							if($c == '%') {
								$c = '%' . $c;
							}
							$formatStr .= $c;
						}
					}
					break;
				}
				case self::IN_NUMBER: {
					if(!in_array($c, $numberChars)) {
						if($numberState == 'inInteger') {
							$groupingDistances[] = $currentGroupingDistance;
						}
						$formatStr .= '%1$s';
						--$i;
						$state = self::IN_POSTFIX;
					} else {

						if($numberState == 'inInteger') {
							if($c == ',') {
								$groupingDistances[] = $currentGroupingDistance;
								$currentGroupingDistance = 0;
							} elseif($c == '.') {
								$groupingDistances[] = $currentGroupingDistance;
								// if we have a dot we default to show the entire fractional part
								$maxShowedFractionals = -1;
								$numberState = 'inFraction';
							} else {
								// when the user has a pattern like 0##0 the 2 ## are mandatory too
								// (basicly everything after the first 0 is mandatory, so take care here)
								if($minShowedIntegrals > 0) {
									++$minShowedIntegrals;
								} elseif($c == '0') {
									++$minShowedIntegrals;
								}
								++$currentGroupingDistance;
							}
						} elseif($numberState == 'inFraction') {
							if($c == ',' || $c == '.') {
								throw new AgaviException($c. ' is not allowed in the fraction part of the number');
							} else {
								if($c == '#') {
									++$skippedFractionals;
								} elseif($c == '0') {
									++$minShowedFractionals;
									$minShowedFractionals += $skippedFractionals;
									$maxShowedFractionals = $minShowedFractionals;
									$skippedFractionals = 0;
								}
							}
						}
					}

					break;
				}
			}
		}


		if($state == self::IN_NUMBER) {
			if($numberState == 'inInteger') {
				$groupingDistances[] = $currentGroupingDistance;
			}
			$formatStr .= '%1$s';
		}

		// when the user had 0.00# as format (the fractional part ended with an #)
		// the max numbers of the fractional part is unlimited
		if($skippedFractionals) {
			$maxShowedFractionals = -1;
		}


		// we chop of the first element of the grouping distance which is 
		// either the the number of chars until the first ',' or the only element
		// in case there was no grouping separator specified (which means that 
		// there won't be grouping at all)
		array_shift($groupingDistances);

		// now we reverse the array so we can process it in natural order later
		$groupingDistances = array_reverse($groupingDistances);


		// store all info

		$this->formatString = $formatStr;

		$this->minShowedIntegrals = $minShowedIntegrals;
		$this->minShowedFractionals = $minShowedFractionals;
		$this->maxShowedFractionals = $maxShowedFractionals;

		$this->hasMinus = $hasMinus;
		$this->hasCurrency = $hasCurrency;

		$this->groupingDistances = $groupingDistances;
	}

	protected function prepareNumber($number, $currencySymbol)
	{
		$isNegative = false;
		$integralPart = '';
		$fractionalPart = '';
		if(is_float($number)) {
			// since we would overflow when converting to int and calculating the 
			// parts ourselves we simply convert it to a string and let that method
			// handle it
			$number = (string) $number;
		}
		if(is_int($number)) {
			if(abs($number) != $number) {
				$isNegative = true;
				$number = abs($number);
			}
			$integralPart = (string) $number;
		} else {
			$number = trim($number);
			$len = strlen($number);
			// empty string will result in 0
			if($len == 0) {
				$integralPart = '0';
			} else {
				$i = 0;
				if($number[0] == '-') {
					$isNegative = true;
					++$i;
				}
				$inIntegral = true;
				while($i < $len) {
					$c = $number[$i];
					if($inIntegral) {
						if($c == '.') {
							$inIntegral = false;
						} else {
							$integralPart .= $c;
						}
					} else {
						$fractionalPart .= $c;
					}
					++$i;
				}
			}
		}

		$integralLen = strlen($integralPart);
		$fractionalLen = strlen($fractionalPart);

		if($integralLen < $this->minShowedIntegrals) {
			$integralPart = str_repeat('0', $this->minShowedIntegrals - $integralLen) . $integralPart;
		}

		if($fractionalLen < $this->minShowedFractionals) {
			$fractionalPart .= str_repeat('0', $this->minShowedFractionals - $fractionalLen);
		}

		if($this->maxShowedFractionals >= 0 && strlen($fractionalPart) > $this->maxShowedFractionals) {
			$nextDigit = (int) $fractionalPart[$this->maxShowedFractionals];
			$fractionalPart = substr($fractionalPart, 0, $this->maxShowedFractionals);
			if($this->roundingMode != self::ROUND_NONE) {
				$inIntegral = $this->maxShowedFractionals == 0;

				$roundUp = false;
				switch($this->roundingMode) {
					case self::ROUND_SCIENTIFIC:
						$roundUp = $nextDigit > 4;
						break;
					case self::ROUND_FINANCIAL:
						$roundUp = $nextDigit > 5;
						break;
					/* we don't need to do anything on floor
					case self::ROUND_FLOOR:
						break;
					*/
					case self::ROUND_CEIL:
						$roundUp = true;
						break;
				}

				if($roundUp){
					$integralLen = strlen($integralPart);
					if($inIntegral) {
						$pos = $integralLen - 1;
					} else {
						$pos = strlen($fractionalPart) - 1;
					}
					do {
						$roundUp = false;
						if($inIntegral) {
							if($pos < 0) {
								// when we reached the left side of the integral part we insert
								// 1 there and stop
								$integralPart = '1'. $integralPart;
							} else {
								$digit = (int) $integralPart[$pos];
								if($digit == 9) {
									$roundUp = true;
									$digit = 0;
								} else {
									++$digit;
								}
								$integralPart[$pos] = $digit;
								--$pos;
							}
						} else {
							$digit = (int) $fractionalPart[$pos];
							if($digit == 9) {
								$roundUp = true;
								$digit = 0;
							} else {
								++$digit;
							}
							$fractionalPart[$pos] = $digit;
							--$pos;
							if($pos < 0) {
								$inIntegral = true;
								$pos = $integralLen - 1;
							}
						}
					} while($roundUp);
				}
			}
		}

		$gd = $this->groupingDistances;

		if(($gdCount = count($gd)) > 0) {
			$newIntegralPart = '';

			$gdPos = 0;
			$stepsSinceLastGroup = 0;
			for($i = strlen($integralPart) - 1; $i >= 0; --$i) {
				if($stepsSinceLastGroup == $gd[$gdPos]) {
					$newIntegralPart .= $this->groupingSeparator;
					$stepsSinceLastGroup = 0;
					++$gdPos;
				}

				$newIntegralPart .= $integralPart[$i];
				++$stepsSinceLastGroup;

				if($gdPos >= $gdCount) {
					$gdPos = 0;
				}
			}

			$integralPart = strrev($newIntegralPart);
		}

		$number = $integralPart;
		if(strlen($fractionalPart) > 0) {
			$number .= $this->decimalSeparator . $fractionalPart;
		}

		if($isNegative && !$this->hasMinus) {
			$number = '-' . $number;
		}

		return array($number, $isNegative ? '-' : '', $currencySymbol);
	}

	public function formatNumber($number)
	{
		return vsprintf($this->formatString, $this->prepareNumber($number, ''));
	}

	public function formatCurrency($number, $currencySymbol)
	{
		return vsprintf($this->formatString, $this->prepareNumber($number, $currencySymbol));
	}
}

?>
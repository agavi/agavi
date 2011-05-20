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
 * AgaviLdmlConfigHandler allows you to parse ldml files into an array.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviLdmlConfigHandler extends AgaviConfigHandler
{
	protected $nodeRefs = array();

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		$pathParts = pathinfo($config);
		// unlike basename, filename does not contain the extension, which is what we need there
		$lookupPaths = AgaviLocale::getLookupPath($pathParts['filename']);
		$lookupPaths[] = 'root';

		$data = array(
			'layout' => array('orientation' => array('lines' => 'top-to-bottom', 'characters' => 'left-to-right')),
		);

		foreach(array_reverse($lookupPaths) as $basename) {
			$filePath = $pathParts['dirname'] . '/' . $basename . '.' . $pathParts['extension'];
			if(is_readable($filePath)) {
				$ldmlTree = AgaviConfigCache::parseConfig($filePath, false, $this->getValidationFile(), $this->parser);
				$this->prepareParentInformation($ldmlTree);
				$this->parseLdmlTree($ldmlTree->ldml, $data);
			}
		}

		$dayMap = array(
										'sun' => AgaviDateDefinitions::SUNDAY,
										'mon' => AgaviDateDefinitions::MONDAY,
										'tue' => AgaviDateDefinitions::TUESDAY,
										'wed' => AgaviDateDefinitions::WEDNESDAY,
										'thu' => AgaviDateDefinitions::THURSDAY,
										'fri' => AgaviDateDefinitions::FRIDAY,
										'sat' => AgaviDateDefinitions::SATURDAY,
		);

		// fix the day indices for all day fields
		foreach($data['calendars'] as $calKey => &$calValue) {
			// skip the 'default' => '' key => value pair
			if(is_array($calValue)) {
				if(isset($calValue['days']['format'])) {
					foreach($calValue['days']['format'] as $formatKey => &$formatValue) {
						if(is_array($formatValue)) {
							$newData = array();
							foreach($formatValue as $day => $value) {
								$newData[$dayMap[$day]] = $value;
							}
							$formatValue = $newData;
						}
					}
				}

				if(isset($calValue['days']['stand-alone'])) {
					foreach($calValue['days']['stand-alone'] as $formatKey => &$formatValue) {
						if(is_array($formatValue)) {
							$newData = array();
							foreach($formatValue as $day => $value) {
								$newData[$dayMap[$day]] = $value;
							}
							$formatValue = $newData;
						}
					}
				}
			}
		}

		$code = array();
		$code[] = 'return ' . var_export($data, true) . ';';

		return $this->generate($code, $config);
	}

	/**
	 * Prepares the parent information for the given ldml tree.
	 *
	 * @param      AgaviConfigValueHolder The ldml tree.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function prepareParentInformation($ldmlTree)
	{
		$this->nodeRefs = array();
		$i = 0;
		$ldmlTree->setAttribute('__agavi_node_id', $i);
		$ldmlTree->setAttribute('__agavi_parent_id', null);
		$this->nodeRefs[$i] = $ldmlTree;
		++$i;
		if($ldmlTree->hasChildren()) {
			$this->generateParentInformation($ldmlTree->getChildren(), $i, 0);
		}
	}

	/**
	 * Generates the parent information for the given ldml subtree.
	 *
	 * @param      AgaviConfigValueHolder The ldml node.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function generateParentInformation($childList, &$nextId, $parentId)
	{
		foreach($childList as $child) {
			$child->setAttribute('__agavi_node_id', $nextId);
			$child->setAttribute('__agavi_parent_id', $parentId);
			$this->nodeRefs[$nextId] = $child;
			++$nextId;
			if($child->hasChildren()) {
				$this->generateParentInformation($child->getChildren(), $nextId, $child->getAttribute('__agavi_node_id'));
			}
		}
	}

/*


array data format


 locale                  =
   language              = de|en|fr|..
   territory             = DE|AT|CH|..
   script                = Latn|...
   variant               = NYNORSK|...


 display Names           =
   languages             =
     [lId]               = localized language 
   scripts               =
     [sId]               = localized script name
   territories           = 
     [tId]               = localized territory name
   variants              = 
     [vId]               = localized variant name
   keys                  = 
     [key]               = localized key name
   measurementSystemNames=
     [mId]               = localized measurement system name
 

 layout                  =
   orientation           =
     lines               = top-to-bottom|bottom-to-top
     characters          = left-to-right|right-to-left

 delimiters              =
   quotationStart        = The quotation start symbol
   quotationEnd          = The quotation end symbol
   altQuotationStart     = The alternative quotation start symbol
   altQuotationEnd       = The alternative quotation end symbol


 calendars               =
   default               = The default calendar
   [cId]                 =
     months              =
       default           = format|stand-alone
       format            =
         default         = wide|abbreviated|narrow
         wide            =
           1|2|3|...     = The wide month name
         abbreviated     = 
           1|2|3|...     = The abbreviated month name
         narrow          = 
           1|2|3|...     = The narrow month name
       stand-alone       =
         default         = wide|abbreviated|narrow
         wide            =
           1|2|3|...     = The wide month name
         abbreviated     = 
           1|2|3|...     = The abbreviated month name
         narrow          = 
           1|2|3|...     = The narrow month name
     days                =
       default           = format|stand-alone
       format            =
         default         = wide|abbreviated|narrow
         wide            =
           mon|tue|...   = The wide day name
         abbreviated     = 
           ...
     quarters            =
       default           = format|stand-alone
       format            =
         default         = wide|abbreviated|narrow
         wide            =
           1|2|3|4       = The wide quarter name
         abbreviated     = 
           ...
     am                  = The locale string for am
     pm                  = The locale string for pm
     eras                =
       wide              =
         1|2|3|...       = The wide era name
       abbreviated       = 
         1|2|3|...       = The abbreviated era name
       narrow            = 
         1|2|3|...       = The narrow era name
     dateFormats         =
       default           = full|long|medium|short
       [dfId]            =
         pattern         = The date pattern
         displayName     = An optional format name
     timeFormats         =
       default           = full|long|medium|short
       [tfId]            =
         pattern         = The time pattern
         displayName     = An optional format name

     dateTimeFormats     =
       default           = full|long|medium|short
       formats           =
         [fId]           = pattern
       availableFormats  =
         [afId]          = The datetime pattern
       appendItems       =
         [aiId]          = pattern

     fields              =
       [fId]             =
         displayName     = The localized name for this field
         relatives       =
           [rId]         = The localized relative of this field

 timeZoneNames
   hourFormat            = 
   hoursFormat           = 
   gmtFormat             = 
   regionFormat          = 
   fallbackFormat        = 
   abbreviationFallback  = standard|...?
   singleCountries       =
     [id]                = timezone
   zones                 =
     [tzId]              =
       long              =
         generic         = 
         standard        = 
         daylight        = 
       short
         generic         = 
         standard        = 
         daylight        = 
       exemplarCity      = 

 numbers                 =
   symbols               =
     decimal             = .
     group               = ,
     list                = ;
     percentSign         = %
     nativeZeroDigit     = 0
     patternDigit        = #
     plusSign            = +
     minusSign           = -
     exponential         = E
     perMille            = ‰
     infinity            = ∞
     nan                 = ☹
   decimalFormats        =
     [dfId]              = pattern
   scientificFormats     =
     [sfId]              = pattern
   percentFormats        =
     [pfId]              = pattern
   currencyFormats       =
     [cfId]              = pattern
   currencySpacing       =
     beforeCurrency      =
       currencyMatch     = 
       surroundingMatch  = 
       insertBetween     = 
     afterCurrency       =
       currencyMatch     = 
       surroundingMatch  = 
       insertBetween     = 
   currencies            =
     [cId]               =
       displayName       = The locale display name
       symbol            = The symbol (or array when its a choice)

			
*/

	/**
	 * Generates the array used by AgaviLocale from an LDML tree.
	 *
	 * @param      AgaviConfigValueHolder The ldml tree.
	 * @param      array The array to store the parsed data to.
	 *
	 * @return     array The array with the data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parseLdmlTree($ldmlTree, &$data)
	{

		if(isset($ldmlTree->identity)) {
			$data['locale']['language'] = $ldmlTree->identity->language->getAttribute('type');
			if(isset($ldmlTree->identity->territory)) {
				$data['locale']['territory'] = $ldmlTree->identity->territory->getAttribute('type');
			}
			if(isset($ldmlTree->identity->script)) {
				$data['locale']['script'] = $ldmlTree->identity->script->getAttribute('type');
			}
			if(isset($ldmlTree->identity->variant)) {
				$data['locale']['variant'] = $ldmlTree->identity->variant->getAttribute('type');
			}
		}

		if(isset($ldmlTree->localeDisplayNames)) {
			$ldn = $ldmlTree->localeDisplayNames;

			if(isset($ldn->languages)) {
				$data['displayNames']['languages'] = isset($data['displayNames']['languages']) ? $data['displayNames']['languages'] : array();
				$this->getTypeList($ldn->languages, $data['displayNames']['languages']);
			}

			if(isset($ldn->scripts)) {
				$data['displayNames']['scripts'] = isset($data['displayNames']['scripts']) ? $data['displayNames']['scripts'] : array();
				$this->getTypeList($ldn->scripts, $data['displayNames']['scripts']);
			}

			if(isset($ldn->territories)) {
				$data['displayNames']['territories'] = isset($data['displayNames']['territories']) ? $data['displayNames']['territories'] : array();
				$this->getTypeList($ldn->territories, $data['displayNames']['territories'], false);
			}

			if(isset($ldn->variants)) {
				$data['displayNames']['variants'] = isset($data['displayNames']['variants']) ? $data['displayNames']['variants'] : array();
				$this->getTypeList($ldn->variants, $data['displayNames']['variants']);
			}

			if(isset($ldn->keys)) {
				$data['displayNames']['keys'] = isset($data['displayNames']['keys']) ? $data['displayNames']['keys'] : array();
				$this->getTypeList($ldn->keys, $data['displayNames']['keys']);
			}

			/*
			// not needed right now
			if(isset($ldn->types)) {
			}
			*/

			if(isset($ldn->measurementSystemNames)) {
				$data['displayNames']['measurementSystemNames'] = isset($data['displayNames']['measurementSystemNames']) ? $data['displayNames']['measurementSystemNames'] : array();
				$this->getTypeList($ldn->measurementSystemNames, $data['displayNames']['measurementSystemNames']);
			}
		}

		if(isset($ldmlTree->layout->orientation)) {
			$ori = $ldmlTree->layout->orientation;

			$data['layout']['orientation']['lines'] = $ori->getAttribute('lines', $data['layout']['orientation']['lines']);
			$data['layout']['orientation']['characters'] = $ori->getAttribute('characters', $data['layout']['orientation']['characters']);
		}

		if(isset($ldmlTree->delimiters)) {
			$delims = $ldmlTree->delimiters;

			if(isset($delims->quotationStart)) {
				$data['delimiters']['quotationStart'] = $this->unescape($delims->quotationStart->getValue());
			}
			if(isset($delims->quotationEnd)) {
				$data['delimiters']['quotationEnd'] = $this->unescape($delims->quotationEnd->getValue());
			}
			if(isset($delims->alternateQuotationStart)) {
				$data['delimiters']['alternateQuotationStart'] = $this->unescape($delims->alternateQuotationStart->getValue());
			}
			if(isset($delims->alternateQuotationEnd)) {
				$data['delimiters']['alternateQuotationEnd'] = $this->unescape($delims->alternateQuotationEnd->getValue());
			}
		}

		if(isset($ldmlTree->dates)) {
			$dates = $ldmlTree->dates;

			if(isset($dates->calendars)) {
				$cals = $dates->calendars;

				foreach($cals as $calendar) {

					if($calendar->getName() == 'default') {
						$data['calendars']['default'] = $calendar->getAttribute('choice');
					} elseif($calendar->getName() == 'calendar') {
						$calendarName = $calendar->getAttribute('type');

						if(!isset($data['calendars'][$calendarName])) {
							$data['calendars'][$calendarName] = array();
						}

						if(isset($calendar->months)) {
							$this->getCalendarWidth($calendar->months, 'month', $data['calendars'][$calendarName]);
						}

						if(isset($calendar->days)) {
							$this->getCalendarWidth($calendar->days, 'day', $data['calendars'][$calendarName]);
						}

						if(isset($calendar->quarters)) {
							$this->getCalendarWidth($calendar->quarters, 'quarter', $data['calendars'][$calendarName]);
						}

						if(isset($calendar->am)) {
							$data['calendars'][$calendarName]['am'] = $this->unescape($calendar->am->getValue());
						}
						if(isset($calendar->pm)) {
							$data['calendars'][$calendarName]['pm'] = $this->unescape($calendar->pm->getValue());
						}

						if(isset($calendar->eras)) {
							if(isset($calendar->eras->eraNames)) {
								foreach($this->getChildsOrAlias($calendar->eras->eraNames) as $era) {
									$data['calendars'][$calendarName]['eras']['wide'][$era->getAttribute('type')] = $this->unescape($era->getValue());
								}
							}
							if(isset($calendar->eras->eraAbbr)) {
								foreach($this->getChildsOrAlias($calendar->eras->eraAbbr) as $era) {
									$data['calendars'][$calendarName]['eras']['abbreviated'][$era->getAttribute('type')] = $this->unescape($era->getValue());
								}
							}
							if(isset($calendar->eras->eraNarrow)) {
								foreach($this->getChildsOrAlias($calendar->eras->eraNarrow) as $era) {
									$data['calendars'][$calendarName]['eras']['narrow'][$era->getAttribute('type')] = $this->unescape($era->getValue());
								}
							}
						}

						if(isset($calendar->dateFormats)) {
							$this->getDateOrTimeFormats($calendar->dateFormats, 'dateFormat', $data['calendars'][$calendarName]);
						}
						if(isset($calendar->timeFormats)) {
							$this->getDateOrTimeFormats($calendar->timeFormats, 'timeFormat', $data['calendars'][$calendarName]);
						}

						if(isset($calendar->dateTimeFormats)) {
							$dtf = $calendar->dateTimeFormats;
							$data['calendars'][$calendarName]['dateTimeFormats']['default'] = isset($dtf->default) ? $dtf->default->getAttribute('choice') : '__default';

							$dtfItems = $this->getChildsOrAlias($dtf);
							foreach($dtfItems as $item) {
								if($item->getName() == 'dateTimeFormatLength') {
									if(isset($item->dateTimeFormat->pattern)) {
										$data['calendars'][$calendarName]['dateTimeFormats']['formats'][$item->getAttribute('type', '__default')] = $this->unescape($item->dateTimeFormat->pattern->getValue(), true);
									} else {
										throw new AgaviException('unknown child content in dateTimeFormatLength tag');
									}
								} elseif($item->getName() == 'availableFormats') {
									foreach($item as $dateFormatItem) {
										if($dateFormatItem->getName() != 'dateFormatItem') {
											throw new AgaviException('unknown childtag "' . $dateFormatItem->getName() . '" in availableFormats tag');
										}
										$data['calendars'][$calendarName]['dateTimeFormats']['availableFormats'][$dateFormatItem->getAttribute('id')] = $this->unescape($dateFormatItem->getValue(), true);
									}
								} elseif($item->getName() == 'appendItems') {
									foreach($item as $appendItem) {
										if($appendItem->getName() != 'appendItem') {
											throw new AgaviException('unknown childtag "' . $appendItem->getName() . '" in appendItems tag');
										}
										$data['calendars'][$calendarName]['dateTimeFormats']['appendItems'][$appendItem->getAttribute('request')] = $this->unescape($appendItem->getValue(), true);
									}
								} elseif($item->getName() != 'default') {
									throw new AgaviException('unknown childtag "' . $item->getName() . '" in dateTimeFormats tag');
								}
							}
						}

						if(isset($calendar->fields)) {
							foreach($this->getChildsOrAlias($calendar->fields) as $field) {
								$type = $field->getAttribute('type');
								if(isset($field->displayName)) {
									$data['calendars'][$calendarName]['fields'][$type]['displayName'] = $this->unescape($field->displayName->getValue());
								}
								if(isset($field->relative)) {
									foreach($field as $relative) {
										if($relative->getName() == 'relative') {
											$data['calendars'][$calendarName]['fields'][$type]['relatives'][$relative->getAttribute('type')] = $this->unescape($relative->getValue());
										}
									}
								}
							}
						}
					} else {
						throw new Exception('unknown childtag "' . $calendar->getName() . '" in calendars tag');
					}
				}
			}
			
			if(isset($dates->timeZoneNames)) {
				$tzn = $dates->timeZoneNames;
				if(isset($tzn->hourFormat)) {
					$data['timeZoneNames']['hourFormat'] = $this->unescape($tzn->hourFormat->getValue());
				}
				if(isset($tzn->hoursFormat)) {
					$data['timeZoneNames']['hoursFormat'] = $this->unescape($tzn->hoursFormat->getValue());
				}
				if(isset($tzn->gmtFormat)) {
					$data['timeZoneNames']['gmtFormat'] = $this->unescape($tzn->gmtFormat->getValue());
				}
				if(isset($tzn->regionFormat)) {
					$data['timeZoneNames']['regionFormat'] = $this->unescape($tzn->regionFormat->getValue());
				}
				if(isset($tzn->fallbackFormat)) {
					$data['timeZoneNames']['fallbackFormat'] = $this->unescape($tzn->fallbackFormat->getValue());
				}
				if(isset($tzn->abbreviationFallback)) {
					$data['timeZoneNames']['abbreviationFallback'] = $tzn->abbreviationFallback->getAttribute('choice');
				}
				if(isset($tzn->singleCountries)) {
					$data['timeZoneNames']['singleCountries'] = explode(' ', $tzn->singleCountries->getAttribute('list'));
				}

				foreach($tzn as $zone) {
					$zoneName = $zone->getAttribute('type');
					if($zone->getName() == 'zone') {
						if(isset($zone->long->generic)) {
							$data['timeZoneNames']['zones'][$zoneName]['long']['generic'] = $this->unescape($zone->long->generic->getValue());
						}
						if(isset($zone->long->standard)) {
							$data['timeZoneNames']['zones'][$zoneName]['long']['standard'] = $this->unescape($zone->long->standard->getValue());
						}
						if(isset($zone->long->daylight)) {
							$data['timeZoneNames']['zones'][$zoneName]['long']['daylight'] = $this->unescape($zone->long->daylight->getValue());
						}
						if(isset($zone->short->generic)) {
							$data['timeZoneNames']['zones'][$zoneName]['short']['generic'] = $this->unescape($zone->short->generic->getValue());
						}
						if(isset($zone->short->standard)) {
							$data['timeZoneNames']['zones'][$zoneName]['short']['standard'] = $this->unescape($zone->short->standard->getValue());
						}
						if(isset($zone->short->daylight)) {
							$data['timeZoneNames']['zones'][$zoneName]['short']['daylight'] = $this->unescape($zone->short->daylight->getValue());
						}
						if(isset($zone->exemplarCity)) {
							$data['timeZoneNames']['zones'][$zoneName]['exemplarCity'] = $this->unescape($zone->exemplarCity->getValue());
						}

					}
				}
			}
		}

		if(isset($ldmlTree->numbers)) {
			$nums = $ldmlTree->numbers;
			if(!isset($data['numbers'])) {
				$data['numbers'] = array();
			}

			if(isset($nums->symbols)) {
				$syms = $nums->symbols;
				if(isset($syms->decimal)) {
					$data['numbers']['symbols']['decimal'] = $this->unescape($syms->decimal->getValue());
				}
				if(isset($syms->group)) {
					$data['numbers']['symbols']['group'] = $this->unescape($syms->group->getValue());
				}
				if(isset($syms->list)) {
					$data['numbers']['symbols']['list'] = $this->unescape($syms->list->getValue());
				}
				if(isset($syms->percentSign)) {
					$data['numbers']['symbols']['percentSign'] = $this->unescape($syms->percentSign->getValue());
				}
				if(isset($syms->nativeZeroDigit)) {
					$data['numbers']['symbols']['nativeZeroDigit'] = $this->unescape($syms->nativeZeroDigit->getValue());
				}
				if(isset($syms->patternDigit)) {
					$data['numbers']['symbols']['patternDigit'] = $this->unescape($syms->patternDigit->getValue());
				}
				if(isset($syms->plusSign)) {
					$data['numbers']['symbols']['plusSign'] = $this->unescape($syms->plusSign->getValue());
				}
				if(isset($syms->minusSign)) {
					$data['numbers']['symbols']['minusSign'] = $this->unescape($syms->minusSign->getValue());
				}
				if(isset($syms->exponential)) {
					$data['numbers']['symbols']['exponential'] = $this->unescape($syms->exponential->getValue());
				}
				if(isset($syms->perMille)) {
					$data['numbers']['symbols']['perMille'] = $this->unescape($syms->perMille->getValue());
				}
				if(isset($syms->infinity)) {
					$data['numbers']['symbols']['infinity'] = $this->unescape($syms->infinity->getValue());
				}
				if(isset($syms->nan)) {
					$data['numbers']['symbols']['nan'] = $this->unescape($syms->nan->getValue());
				}
			}
			if(isset($nums->decimalFormats)) {
				$this->getNumberFormats($nums->decimalFormats, 'decimalFormat', $data['numbers']);
			}
			if(isset($nums->scientificFormats)) {
				$this->getNumberFormats($nums->scientificFormats, 'scientificFormat', $data['numbers']);
			}
			if(isset($nums->percentFormats)) {
				$this->getNumberFormats($nums->percentFormats, 'percentFormat', $data['numbers']);
			}
			if(isset($nums->currencyFormats)) {
				$cf = $nums->currencyFormats;

				foreach($this->getChildsOrAlias($cf) as $itemLength) {
					if($itemLength->getName() == 'default') {
						$data['numbers']['currencyFormats']['default'] = $itemLength->getAttribute('choice');
					} elseif($itemLength->getName() == 'currencyFormatLength') {
						$itemLengthName = $itemLength->getAttribute('type', '__default');

						foreach($this->getChildsOrAlias($itemLength) as $itemFormat) {
							if($itemFormat->getName() == 'currencyFormat') {
								if(isset($itemFormat->pattern)) {
									$data['numbers']['currencyFormats'][$itemLengthName] = $this->unescape($itemFormat->pattern->getValue());
								}
							} else {
								throw new Exception('unknown childtag "' . $itemFormat->getName() . '" in currencyFormatLength tag');
							}

						}
					} elseif($itemLength->getName() == 'currencySpacing') {

						if(isset($itemLength->beforeCurrency->currencyMatch)) {
							$data['numbers']['currencySpacing']['beforeCurrency']['currencyMatch'] = $this->unescape($itemLength->beforeCurrency->currencyMatch->getValue());
						}
						if(isset($itemLength->beforeCurrency->surroundingMatch)) {
							$data['numbers']['currencySpacing']['beforeCurrency']['surroundingMatch'] = $this->unescape($itemLength->beforeCurrency->surroundingMatch->getValue());
						}
						if(isset($itemLength->beforeCurrency->insertBetween)) {
							$data['numbers']['currencySpacing']['beforeCurrency']['insertBetween'] = $this->unescape($itemLength->beforeCurrency->insertBetween->getValue());
						}
						if(isset($itemLength->afterCurrency->currencyMatch)) {
							$data['numbers']['currencySpacing']['afterCurrency']['currencyMatch'] = $this->unescape($itemLength->afterCurrency->currencyMatch->getValue());
						}
						if(isset($itemLength->afterCurrency->surroundingMatch)) {
							$data['numbers']['currencySpacing']['afterCurrency']['surroundingMatch'] = $this->unescape($itemLength->afterCurrency->surroundingMatch->getValue());
						}
						if(isset($itemLength->afterCurrency->insertBetween)) {
							$data['numbers']['currencySpacing']['afterCurrency']['insertBetween'] = $this->unescape($itemLength->afterCurrency->insertBetween->getValue());
						}

					} else {
						throw new Exception('unknown childtag "' . $itemLength->getName() . '" in currencyFormats tag');
					}
				}
			}
			if(isset($nums->currencies)) {
				foreach($nums->currencies as $currency) {
					$name = $currency->getAttribute('type');
					if(isset($currency->displayName)) {
						$data['numbers']['currencies'][$name]['displayName'] = $this->unescape($currency->displayName->getValue());
					}
					if(isset($currency->symbol)) {
						$symbolValue = $this->unescape($currency->symbol->getValue());
						if($currency->symbol->getAttribute('choice') == 'true') {
							$symbolValue = explode('|', $symbolValue);
						}
						$data['numbers']['currencies'][$name]['symbol'] = $symbolValue;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Gets the value of each node with a type attribute.
	 *
	 * @param      array List of AgaviConfigValueHolder items.
	 * @param      array The array to store the parsed data to.
	 *
	 * @return     array The array with the data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getTypeList($list, &$data)
	{
		// debug stuff to check if we missed any tags (lc = loop count)
		$lc = 0;
		foreach($list as $listItem) {
			$type = $listItem->getAttribute('type');

			if(!$listItem->hasAttribute('alt')) {
				$data[$type] = $this->unescape($listItem->getValue());
			}

			++$lc;
		}

		if($lc != count($list->getChildren())) {
			throw new AgaviException('wrong tagcount');
		}

		return $data;
	}

	/**
	 * Gets the calendar widths for the given item.
	 *
	 * @param      AgaviConfigValueHolder The item.
	 * @param      string The name of item.
	 * @param      array The array to store the parsed data to.
	 *
	 * @return     array The array with the data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getCalendarWidth($item, $name, &$data)
	{
		$dataIdxName = $name . 's';

		$items = $this->getChildsOrAlias($item);
		foreach($items as $itemContext) {
			if($itemContext->getName() == 'default') {
				$data[$dataIdxName]['default'] = $itemContext->getAttribute('choice');
			} elseif($itemContext->getName() == $name . 'Context') {
				$itemContextName = $itemContext->getAttribute('type');

				foreach($itemContext as $itemWidths) {
					if($itemWidths->getName() == 'default') {
						$data[$dataIdxName][$itemContextName]['default'] = $itemWidths->getAttribute('choice');
					} elseif($itemWidths->getName() == $name . 'Width') {
						$itemWidthName = $itemWidths->getAttribute('type');

						$widthChildItems = $this->getChildsOrAlias($itemWidths);
						foreach($widthChildItems as $item) {
							if($item->getName() != $name) {
								throw new Exception('unknown childtag "' . $item->getName() . '" in ' . $name . 'Widths tag');
							}

							if(!$item->hasAttribute('alt')) {
								$itemName = $item->getAttribute('type');
								$data[$dataIdxName][$itemContextName][$itemWidthName][$itemName] = $this->unescape($item->getValue());
							}
						}
					} else {
						throw new Exception('unknown childtag "' . $itemWidths->getName() . '" in ' . $name . 'Context tag');
					}

				}
			} else {
				throw new Exception('unknown childtag "' . $itemContext->getName() . '" in ' . $name . 's tag');
			}
		}
	}

	/**
	 * Gets the date or time formats the given item.
	 *
	 * @param      AgaviConfigValueHolder The item.
	 * @param      string The name of item.
	 * @param      array The array to store the parsed data to.
	 *
	 * @return     array The array with the data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getDateOrTimeFormats($item, $name, &$data)
	{
		$dataIdxName = $name . 's';

		$items = $this->getChildsOrAlias($item);
		foreach($items as $itemLength) {
			if($itemLength->getName() == 'default') {
				$data[$dataIdxName]['default'] = $itemLength->getAttribute('choice');
			} elseif($itemLength->getName() == $name . 'Length') {
				$itemLengthName = $itemLength->getAttribute('type', '__default');

				$aliasedItemLength = $this->getChildsOrAlias($itemLength);
				foreach($aliasedItemLength as $itemFormat) {
					if($itemFormat->getName() == $name) {
						if(isset($itemFormat->pattern)) {
							$data[$dataIdxName][$itemLengthName]['pattern'] = $this->unescape($itemFormat->pattern->getValue(), true);
						}
						if(isset($itemFormat->displayName)) {
							$data[$dataIdxName][$itemLengthName]['displayName'] = $this->unescape($itemFormat->displayName->getValue());
						}
					} else {
						throw new Exception('unknown childtag "' . $itemFormat->getName() . '" in ' . $name . 'Length tag');
					}

				}
			} else {
				throw new Exception('unknown childtag "' . $itemLength->getName() . '" in ' . $name . 's tag');
			}
		}
	}

	/**
	 * Gets the number formats the given item.
	 *
	 * @param      AgaviConfigValueHolder The item.
	 * @param      string The name of item.
	 * @param      array The array to store the parsed data to.
	 *
	 * @return     array The array with the data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getNumberFormats($item, $name, &$data)
	{
		$dataIdxName = $name . 's';

		$items = $this->getChildsOrAlias($item);
		foreach($items as $itemLength) {
			if($itemLength->getName() == 'default') {
				$data[$dataIdxName]['default'] = $itemLength->getAttribute('choice');
			} elseif($itemLength->getName() == $name . 'Length') {
				$itemLengthName = $itemLength->getAttribute('type', '__default');

				foreach($this->getChildsOrAlias($itemLength) as $itemFormat) {
					if($itemFormat->getName() == $name) {
						if(isset($itemFormat->pattern)) {
							$data[$dataIdxName][$itemLengthName] = $this->unescape($itemFormat->pattern->getValue());
						}
					} else {
						throw new Exception('unknown childtag "' . $itemFormat->getName() . '" in ' . $name . 'Length tag');
					}

				}
			} else {
				throw new Exception('unknown childtag "' . $itemLength->getName() . '" in ' . $name . 's tag');
			}
		}
	}

	/**
	 * Resolves the alias LDML tag.
	 *
	 * @param      AgaviConfigValueHolder The item.
	 *
	 * @return     mixed Either the item if there is no alias or the resolved 
	 *                   alias.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getChildsOrAlias($item)
	{
		if(isset($item->alias)) {
			$alias = $item->alias;
			if($alias->getAttribute('source') != 'locale') {
				throw new AgaviException('The alias handling doesn\'t support any source except locale (' . $alias->getAttribute('source') . ' was given)');
			}

			$pathParts = explode('/', $alias->getAttribute('path'));

			$currentNodeId = $item->getAttribute('__agavi_node_id');
			
			foreach($pathParts as $part) {
				// select the parent node
				if($part == '..') {
					$currentNodeId = $this->nodeRefs[$currentNodeId]->getAttribute('__agavi_parent_id');
				} else {
					$predicates = array();
					if(preg_match('#([^\[]+)\[([^\]]+)\]#', $part, $match)) {
						if(!preg_match('#@([^=]+)=\'([^\']+)\'#', $match[2], $predMatch)) {
							throw new AgaviException('Unknown predicate ' . $match[2] . ' in alias xpath spec');
						}
						$tagName = $match[1];
						$predicates[$predMatch[1]] = $predMatch[2];
					} else {
						$tagName = $part;
					}
					foreach($this->nodeRefs[$currentNodeId]->getChildren() as $childNode) {
						$isSearchedNode = false;
						if($childNode->getName() == $tagName) {
							$predMatches = 0;
							foreach($predicates as $attrib => $value) {
								if($childNode->getAttribute($attrib) == $value) {
									++$predMatches;
								}
							}
							if($predMatches == count($predicates)) {
								$isSearchedNode = true;
							}
						}

						if($isSearchedNode) {
							$currentNodeId = $childNode->getAttribute('__agavi_node_id');
						}
					}
				}
			}

			return $this->nodeRefs[$currentNodeId]->getChildren();
		} else {
			return $item;
		}
	}
	
	/**
	 * Unescapes unicode escapes
	 * 
	 * @link       http://unicode.org/reports/tr35/#Unicode_Sets
	 * 
	 * @param      string The string to unescape.
	 * @param      bool   Whether the string needs to handle quoting behaviour
	 *
	 * @return     string The unescaped string.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0	
	 */
	protected function unescape($input, $handleQuotes = false)
	{
		$output = '';
		$hex = '[0-9A-Fa-f]';
		$rx = '\\\\(\\\\|u' . $hex . '{4}|U' . $hex . '{8}|x' . $hex .'{1,2}|[0-7]{1,3}|.)';
		if($handleQuotes) {
			// needs to be < -1 to not confuse the algorithm in the first run
			$lastClose = -2;
			if(preg_match_all("#'(?:''|[^'])+'|" . $rx . "#", $input, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
				$output = $input;
				$offsetMod = 0;
				$ml = $matches[0];
				$len = count($ml);
				for($i = 0; $i < $len; ++ $i) {
					$match = $ml[$i];
					if($match[0][0] != '\'') {
						// we check if there is a quoted string directly before or directly after the escape sequence
						// by using the string lengths and the offset of the matches. Since an escape sequence directly before
						// this sequence results in an quoted string we only check if its really a quoted string and not an
						// escape sequence for parts coming after this sequence
						$quoteBefore = ($i > 0 && (strlen($ml[$i - 1][0]) + $ml[$i - 1][1]) == $match[1]);
						$quoteAfter = ($i + 1 < $len && $ml[$i + 1][0][0] == '\'' && (strlen($match[0]) + $match[1]) == $ml[$i + 1][1]);
						$oldLen = strlen($output);
						$unescaped = $this->unescapeCallback(array($match[0], substr($match[0], 1)));
						$unescaped = ($quoteBefore ? '' : '\'') . $unescaped . ($quoteAfter ? '' : '\'');
						$replacedPartLen = strlen($match[0]) + ((int) $quoteBefore) + ((int) $quoteAfter);
						// replace the matched escape sequence with the unescaped one. we also replace the opening or closing quote
						// from the quoted part before or after this escape sequence to include the unescaped string into the closed part
						$output = substr_replace($output, $unescaped, $offsetMod + $match[1] + ($quoteBefore ? -1 : 0), $replacedPartLen);
						// since the string length gets changed, we have to track the size change so we can adjust the offset from the match
						$offsetMod += strlen($output) - $oldLen;
					}
				}
			} else {
				$output = $input;
			}
		} else {
			$output = preg_replace_callback('#' . $rx . '#', array($this, 'unescapeCallback'), $input);
		}

		return $output;
	}
	
	/**
	 * Unescapes a single unicode escape sequence. This is designed to be a 
	 * preg_replace_callback callback function.
	 * 
	 * @param      array The match.
	 *
	 * @return     string The unescaped sequence.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0	
	 */
	protected function unescapeCallback($matches)
	{
		static $map = array(
			'a' => "\x07",
			'b' => "\x08",
			't' => "\x09",
			'n' => "\x0A",
			'v' => "\x0B",
			'f' => "\x0C",
			'r' => "\x0D",
			'\\' => '\\',
		);
		
		
		$res = '';
		
		$char = $matches[1][0];
		$seq = substr($matches[1], 1);
		if($char == 'u' || $char == 'U' || $char == 'x') {
			$res = html_entity_decode('&#x' . $seq . ';', ENT_QUOTES, 'utf-8');
		} elseif(is_numeric($char)) {
			$res = html_entity_decode('&#' . octdec($matches[1]) . ';', ENT_QUOTES, 'utf-8');
		} elseif(isset($map[$char])) {
			$res = $map[$char];
		} else {
			$res = $char; // something like \s or \0 or so, just return the character ("s" or "0")
		}
		
		return $res;
	}
}

?>
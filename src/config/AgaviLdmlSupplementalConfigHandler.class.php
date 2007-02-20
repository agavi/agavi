<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviLdmlSupplementalConfigHandler allows you to parse ldml supplemental data
 * file into an array.
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
class AgaviLdmlSupplementalConfigHandler extends AgaviConfigHandler
{
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
		$dayMap = array(
										'sun' => AgaviDateDefinitions::SUNDAY,
										'mon' => AgaviDateDefinitions::MONDAY,
										'tue' => AgaviDateDefinitions::TUESDAY,
										'wed' => AgaviDateDefinitions::WEDNESDAY,
										'thu' => AgaviDateDefinitions::THURSDAY,
										'fri' => AgaviDateDefinitions::FRIDAY,
										'sat' => AgaviDateDefinitions::SATURDAY,
		);


		$dataTree = AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->supplementalData;

		$parsedData = array();
		$data = array();

		foreach($dataTree->currencyData as $currencyNode) {
			if($currencyNode->getName() == 'fractions') {
				foreach($currencyNode as $info) {
					$data['fractions'][$info->getAttribute('iso4217')] = array(
						'digits' => $info->getAttribute('digits', 2),
						'rounding' => $info->getAttribute('rounding', 1),
					);
				}
			} elseif($currencyNode->getName() == 'region') {
				foreach($currencyNode as $currency) {
					if($currency->getName() == 'currency') {
						$data['territories'][$currencyNode->getAttribute('iso3166')]['currencies'][$currency->getAttribute('iso4217')] = array(
							'currency' => $currency->getAttribute('iso4217'), 
							'from' => $currency->getAttribute('from'),
							'to' => $currency->getAttribute('to'),
						);
					} else {
						throw new AgaviException('Invalid tag ' . $currency->getName() . ' in region tag');
					}
				}
			} else {
				throw new AgaviException('Invalid tag ' . $currencyNode->getName() . ' in currencyData tag');
			}
		}

		foreach($dataTree->territoryContainment as $group) {
			if($group->getName() == 'group') {
				$data['territoryContainment'][$group->getAttribute('type')] = explode(' ', $group->getAttribute('contains'));
			} else {
				throw new AgaviException('Invalid tag ' . $group->getName() . ' in territoryContainment tag');
			}
		}

		foreach($dataTree->languageData as $language) {
			if($language->getName() == 'language') {
				$lang = $language->getAttribute('type');
				$scripts = explode(' ', $language->getAttribute('scripts'));
				$territories = explode(' ', $language->getAttribute('territories'));
				$alt = $language->getAttribute('alt', 'primary');

				foreach($scripts as $script) {
					$parsedData['languages'][$lang][$alt][$script] = $territories;
				}

				foreach($territories as $territory) {
					$data['territories'][$territory]['languages'][$alt][$lang] = $scripts;
				}
			} else {
				throw new AgaviException('Invalid tag ' . $language->getName() . ' in languageData tag');
			}
		}

		// set the default calendar to gregorian for all territories first
		foreach($data['territories'] as &$territoryData) {
			$territoryData['calendar'] = 'gregorian';
		}

		foreach($dataTree->calendarData as $calendar) {
			$type = $calendar->getAttribute('type');
			foreach(explode(' ', $calendar->getAttribute('territories')) as $territory) {
				$data['territories'][$territory]['calendar'] = $type;
			}
		}

		foreach($dataTree->weekData as $entry) {
			$entryName = $entry->getName();
			if($entryName == 'minDays') {
				foreach(explode(' ', $entry->getAttribute('territories')) as $territory) {
					$countries = $this->resolveTerritoryToCountries($data['territoryContainment'], $territory);
					foreach($countries as $country) {
						$data['territories'][$country]['week'][$entryName] = $entry->getAttribute('count');
					}
				}
			} elseif($entryName == 'firstDay' || $entryName == 'weekendStart' || $entryName == 'weekendEnd') {
				if(!$entry->hasAttribute('alt')) {
					foreach(explode(' ', $entry->getAttribute('territories')) as $territory) {
						$countries = $this->resolveTerritoryToCountries($data['territoryContainment'], $territory);
						foreach($countries as $country) {
							$data['territories'][$country]['week'][$entryName] = $dayMap[$entry->getAttribute('day')];
						}
					}
				}
			} else {
				throw new AgaviException('Invalid tag ' . $entry->getName() . ' in weekData tag');
			}

		}

		$data['timezones'] = array('territories' => array(), 'multiZones' => array());
		foreach(explode(' ', $dataTree->timezoneData->zoneFormatting->getAttribute('multizone')) as $zone) {
			$data['timezones']['multiZones'][$zone] = true;
		}

		foreach($dataTree->timezoneData->zoneFormatting as $zoneItem) {
			if($zoneItem->getName() == 'zoneItem') {
				$zone = $zoneItem->getAttribute('type');
				$territory = $zoneItem->getAttribute('territory');
				$data['timezones']['territories'][$zone] = $territory;
			} else {
				throw new AgaviException('Invalid tag ' . $language->getName() . ' in zoneFormatting tag');
			}
		}

		$code = array();
		$code[] = 'return ' . var_export($data, true) . ';';

		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by ".__CLASS__."\n" .
				  "// date: %s GMT\n%s\n?>";
		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), join("\n", $code));

		return $retval;
	}

	protected function resolveTerritoryToCountries($territoryContainments, $territory)
	{
		if(!isset($territoryContainments[$territory])) {
			return (array) $territory;
		}
		$resultCountries = array();
		
		$territories = $territoryContainments[$territory];
		do {
			$newTerrs = array();
			foreach($territories as $terr) {
				if(isset($territoryContainments[$terr])) {
					foreach($territoryContainments[$terr] as $resolvedTerr) {
						if(is_numeric($resolvedTerr)) {
							$newTerrs[] = $resolvedTerr;
						} else {
							$resultCountries[] = $resolvedTerr;
						}
					}
				} else {
					$resultCountries[] = $terr;
				}
			}
			$territories = $newTerrs;
		} while(count($territories));

		return $resultCountries;
	}
}

?>
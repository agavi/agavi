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
 * AgaviLdmlSupplementalConfigHandler allows you to parse ldml supplemental data
 * file into an array.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviLdmlSupplementalConfigHandler extends AgaviXmlConfigHandler
{
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
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

		$dataTree = $document->documentElement;

		$parsedData = array();
		$data = array();

		foreach($dataTree->getChild('currencyData') as $currencyNode) {
			if($currencyNode->localName == 'fractions') {
				foreach($currencyNode as $info) {
					$data['fractions'][$info->getAttribute('iso4217')] = array(
						'digits' => $info->getAttribute('digits', 2),
						'rounding' => $info->getAttribute('rounding', 1),
					);
				}
			} elseif($currencyNode->localName == 'region') {
				foreach($currencyNode as $currency) {
					if($currency->getName() == 'currency') {
						$data['territories'][$currencyNode->getAttribute('iso3166')]['currencies'][$currency->getAttribute('iso4217')] = array(
							'currency' => $currency->getAttribute('iso4217'), 
							'from' => $currency->getAttribute('from'),
							'to' => $currency->getAttribute('to'),
						);
					} else {
						throw new AgaviException('Invalid tag ' . $currency->localName . ' in region tag');
					}
				}
			} else {
				throw new AgaviException('Invalid tag ' . $currencyNode->localName . ' in currencyData tag');
			}
		}

		foreach($dataTree->getChild('territoryContainment') as $group) {
			if($group->localName == 'group') {
				$data['territoryContainment'][$group->getAttribute('type')] = explode(' ', $group->getAttribute('contains'));
			} else {
				throw new AgaviException('Invalid tag ' . $group->localName . ' in territoryContainment tag');
			}
		}

		foreach($dataTree->getChild('languageData') as $language) {
			if($language->localName == 'language') {
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

		foreach($dataTree->getChild('calendarData') as $calendar) {
			$type = $calendar->getAttribute('type');
			foreach(explode(' ', $calendar->getAttribute('territories')) as $territory) {
				$data['territories'][$territory]['calendar'] = $type;
			}
		}

		foreach($dataTree->getChild('weekData') as $entry) {
			$entryName = $entry->localName;
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
				throw new AgaviException('Invalid tag ' . $entry->localName . ' in weekData tag');
			}

		}

		$data['timezones'] = array('territories' => array(), 'multiZones' => array());
		foreach(explode(' ', $dataTree->getChild('timezoneData')->getChild('zoneFormatting')->getAttribute('multizone')) as $zone) {
			$data['timezones']['multiZones'][$zone] = true;
		}

		foreach($dataTree->getChild('timezoneData')->getChild('zoneFormatting') as $zoneItem) {
			if($zoneItem->localName == 'zoneItem') {
				$zone = $zoneItem->getAttribute('type');
				$territory = $zoneItem->getAttribute('territory');
				$data['timezones']['territories'][$zone] = $territory;
			} else {
				throw new AgaviException('Invalid tag ' . $language->localName . ' in zoneFormatting tag');
			}
		}

		$code = array();
		$code[] = 'return ' . var_export($data, true) . ';';

		return $this->generate($code, $document->documentURI);
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
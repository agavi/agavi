<?php

/**
 * Data file for timezone "America/Cayman".
 * Compiled from olson file "(unknown)", version (unknown).
 *
 * @package    agavi
 * @subpackage translation
 *
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */

return array (
  'types' => 
  array (
    0 => 
    array (
      'rawOffset' => -18431,
      'dstOffset' => 0,
      'name' => 'KMT',
    ),
    1 => 
    array (
      'rawOffset' => -18000,
      'dstOffset' => 0,
      'name' => 'EST',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -2524502068,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -1827687169,
      'type' => 1,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'dynamic',
    'offset' => -18000,
    'name' => 'E%sT',
    'save' => 3600,
    'start' => 
    array (
      'month' => 2,
      'date' => '8',
      'day_of_week' => -1,
      'time' => 7200000,
      'type' => 0,
    ),
    'end' => 
    array (
      'month' => 10,
      'date' => '1',
      'day_of_week' => -1,
      'time' => 7200000,
      'type' => 0,
    ),
    'startYear' => 1913,
  ),
  'source' => '(unknown)',
  'version' => '(unknown)',
  'name' => 'America/Cayman',
);

?>
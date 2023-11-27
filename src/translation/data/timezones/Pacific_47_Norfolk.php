<?php

/**
 * Data file for timezone "Pacific/Norfolk".
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
      'rawOffset' => 40320,
      'dstOffset' => 0,
      'name' => '+1112',
    ),
    1 => 
    array (
      'rawOffset' => 41400,
      'dstOffset' => 0,
      'name' => '+1130',
    ),
    2 => 
    array (
      'rawOffset' => 41400,
      'dstOffset' => 3600,
      'name' => '+1230',
    ),
    3 => 
    array (
      'rawOffset' => 39600,
      'dstOffset' => 0,
      'name' => '+11',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -2177493112.0,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -599656320.0,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => 152029800.0,
      'type' => 2,
    ),
    3 => 
    array (
      'time' => 162916200.0,
      'type' => 1,
    ),
    4 => 
    array (
      'time' => 1443882600.0,
      'type' => 3,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'dynamic',
    'offset' => 39600,
    'name' => 
    array (
      0 => '+11',
      1 => '+12',
    ),
    'save' => 3600,
    'start' => 
    array (
      'month' => 9,
      'date' => '1',
      'day_of_week' => -1,
      'time' => 7200000.0,
      'type' => 1.0,
    ),
    'end' => 
    array (
      'month' => 3,
      'date' => '1',
      'day_of_week' => -1,
      'time' => 7200000.0,
      'type' => 1.0,
    ),
    'startYear' => 2016,
  ),
  'source' => '(unknown)',
  'version' => '(unknown)',
  'name' => 'Pacific/Norfolk',
);

?>
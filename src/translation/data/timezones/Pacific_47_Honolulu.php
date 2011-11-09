<?php

/**
 * Data file for timezone "Pacific/Honolulu".
 * Compiled from olson file "northamerica", version 8.51.
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
      'rawOffset' => -37800,
      'dstOffset' => 0,
      'name' => 'HST',
    ),
    1 => 
    array (
      'rawOffset' => -37800,
      'dstOffset' => 3600,
      'name' => 'HDT',
    ),
    2 => 
    array (
      'rawOffset' => -36000,
      'dstOffset' => 0,
      'name' => 'HST',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -2334101314,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -1157283000,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => -1155436200,
      'type' => 0,
    ),
    3 => 
    array (
      'time' => -880198200,
      'type' => 1,
    ),
    4 => 
    array (
      'time' => -765376200,
      'type' => 0,
    ),
    5 => 
    array (
      'time' => -712150200,
      'type' => 2,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'static',
    'name' => 'HST',
    'offset' => -36000,
    'startYear' => 1948,
  ),
  'source' => 'northamerica',
  'version' => '8.51',
  'name' => 'Pacific/Honolulu',
);

?>
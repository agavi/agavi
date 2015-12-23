<?php

/**
 * Data file for timezone "Antarctica/Troll".
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
      'rawOffset' => 0,
      'dstOffset' => 0,
      'name' => 'UTC',
    ),
    1 => 
    array (
      'rawOffset' => 0,
      'dstOffset' => 7200,
      'name' => 'CEST',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => 1108166400,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => 1111885200,
      'type' => 1,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'dynamic',
    'offset' => 0,
    'name' => '%s',
    'save' => 7200,
    'start' => 
    array (
      'month' => 2,
      'date' => -1,
      'day_of_week' => 1,
      'time' => 3600000,
      'type' => 2,
    ),
    'end' => 
    array (
      'month' => 9,
      'date' => -1,
      'day_of_week' => 1,
      'time' => 3600000,
      'type' => 2,
    ),
    'startYear' => 2006,
  ),
  'source' => '(unknown)',
  'version' => '(unknown)',
  'name' => 'Antarctica/Troll',
);

?>
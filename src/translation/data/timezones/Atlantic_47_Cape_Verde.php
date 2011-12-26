<?php

/**
 * Data file for timezone "Atlantic/Cape_Verde".
 * Compiled from olson file "africa", version 8.33.
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
      'rawOffset' => -7200,
      'dstOffset' => 0,
      'name' => 'CVT',
    ),
    1 => 
    array (
      'rawOffset' => -7200,
      'dstOffset' => 3600,
      'name' => 'CVST',
    ),
    2 => 
    array (
      'rawOffset' => -3600,
      'dstOffset' => 0,
      'name' => 'CVT',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -1988144756,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -862610400,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => -764118000,
      'type' => 0,
    ),
    3 => 
    array (
      'time' => 186120000,
      'type' => 2,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'static',
    'name' => 'CVT',
    'offset' => -3600,
    'startYear' => 1976,
  ),
  'source' => 'africa',
  'version' => '8.33',
  'name' => 'Atlantic/Cape_Verde',
);

?>
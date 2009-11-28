<?php

/**
 * Data file for timezone "Antarctica/Davis".
 * Compiled from olson file "antarctica", version 8.7.
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
      'rawOffset' => 25200,
      'dstOffset' => 0,
      'name' => 'DAVT',
    ),
    1 => 
    array (
      'rawOffset' => 0,
      'dstOffset' => 0,
      'name' => 'zzz',
    ),
    2 => 
    array (
      'rawOffset' => 18000,
      'dstOffset' => 0,
      'name' => 'DAVT',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -409190400,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -163062000,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => -28857600,
      'type' => 0,
    ),
    3 => 
    array (
      'time' => 1255806000,
      'type' => 2,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'static',
    'name' => 'DAVT',
    'offset' => 18000,
    'startYear' => 2010,
  ),
  'source' => 'antarctica',
  'version' => '8.7',
  'name' => 'Antarctica/Davis',
);

?>
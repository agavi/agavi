<?php

/**
 * Data file for timezone "America/La_Paz".
 * Compiled from olson file "southamerica", version 8.52.
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
      'rawOffset' => -16356,
      'dstOffset' => 0,
      'name' => 'CMT',
    ),
    1 => 
    array (
      'rawOffset' => -16356,
      'dstOffset' => 3600,
      'name' => 'BOST',
    ),
    2 => 
    array (
      'rawOffset' => -14400,
      'dstOffset' => 0,
      'name' => 'BOT',
    ),
  ),
  'rules' => 
  array (
    0 => 
    array (
      'time' => -2524505244,
      'type' => 0,
    ),
    1 => 
    array (
      'time' => -1205954844,
      'type' => 1,
    ),
    2 => 
    array (
      'time' => -1192307244,
      'type' => 2,
    ),
  ),
  'finalRule' => 
  array (
    'type' => 'static',
    'name' => 'BOT',
    'offset' => -14400,
    'startYear' => 1933,
  ),
  'source' => 'southamerica',
  'version' => '8.52',
  'name' => 'America/La_Paz',
);

?>
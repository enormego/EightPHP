<?php
 
/**
 * Cascading message threshold.
 * 
 * Log Thresholds:
 *  0 - Disables logging completely
 *  1 - Error Messages (including PHP errors)
 *  2 - Alert Messages
 *  3 - Informational Messages
 *  4 - Debug Messages
 */
$config['threshold'] = 2;
 
/**
 * Log file directory, relative to application/, or absolute.
 */
$config['directory'] = APPPATH.'logs';
 
/**
 * PHP date format for timestamps.
 * @see http://php.net/date
 */
$config['format'] = 'Y-m-d H:i:s';

<?php
/**
 * @package		System
 * @subpackage	Libraries.Cache
 *
 * memcache server configuration.
 */
$config['servers'] = array
(
	array
	(
		'host' => '127.0.0.1',
		'port' => 11211,
		'persistent' => NO,
	)
);

/**
 * Enable cache data compression.
 */
$config['compression'] = NO;

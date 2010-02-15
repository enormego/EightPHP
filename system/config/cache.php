<?php
/**
 * @package		System
 * @subpackage	Libraries.Cache
 *
 * Cache settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * driver
 * :  Eight comes with [apc][ref-apc], [eaccelerator][ref-eac], [file][ref-fil],
 *    [memcache][ref-mem], [sqlite][ref-sql], and [xcache][ref-xca] drivers.
 *
 * params
 * :  Driver-specific configuration options.
 *
 * lifetime
 * :  Default lifetime of caches in seconds. By default caches are stored for 
 *    thirty minutes. Specific lifetime can also be set when creating a new cache.
 *    Setting this to 0 will never automatically delete caches.
 *
 * requests
 * :  Average number of cache requests that will processed before all expired
 *    caches are deleted (garbage collection). Setting this to 0 will disable
 *    automatic garbage collection.
 *
 * [ref-apc]: http://docs.eight.twenty08.com/book/cache/drivers/apc
 * [ref-eac]: http://docs.eight.twenty08.com/book/cache/drivers/eaccelerator
 * [ref-fil]: http://docs.eight.twenty08.com/book/cache/drivers/file
 * [ref-mem]: http://docs.eight.twenty08.com/book/cache/drivers/memcache
 * [ref-sql]: http://docs.eight.twenty08.com/book/cache/drivers/sqlite
 * [ref-xca]: http://docs.eight.twenty08.com/book/cache/drivers/xcache
 */
$config['default'] = array
(
	'driver'   => 'file',
	'params'   => APPPATH.'cache',
	'lifetime' => 1800,
	'requests' => 1000
);

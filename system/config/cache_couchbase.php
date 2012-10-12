<?php
/**
 *
 * Cache settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * Group Options:
 *  driver   - Cache backend driver. Eight comes with file, database, and memcache drivers.
 *              > File cache is fast and reliable, but requires many filesystem lookups.
 *              > Database cache can be used to cache items remotely, but is slower.
 *              > Memcache is very high performance, but prevents cache tags from being used.
 *				> Couchbase is just like memcache, but the server is a bit more intelligent.
 *
 *  params   - Driver parameters, specific to each driver.
 *
 *  lifetime - Default lifetime of caches in seconds. By default caches are stored for
 *             thirty minutes. Specific lifetime can also be set when creating a new cache.
 *             Setting this to 0 will never automatically delete caches.
 *
 *  requests - Average number of cache requests that will processed before all expired
 *             caches are deleted. This is commonly referred to as "garbage collection".
 *             Setting this to 0 or a negative number will disable automatic garbage collection.
 * 
 * @package	core.config
 */

$config['default'] = array(
	'driver'   => 'couchbase',
	'params'   => array(
							'username'		=>	'',
							'password'		=>	'',
							'bucket'		=>	'default',
							'persistent'	=>	TRUE,
							'servers' 		=> array(
								array(
									'host'			=>	'127.0.0.1',
									'port' 			=>	8091,
								),
							),
						),
	'lifetime' => 1800,
	'requests' => 0,
	'prefix' => '',
);
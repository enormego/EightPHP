<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Memcache-based Cache driver.
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Cache_Driver_Memcache extends Cache_Driver {
	protected $config;
	protected $backend;
	protected $flags;

	public function __construct($config) {
		if(!extension_loaded('memcache'))
			throw new Cache_Exception('The memcache PHP extension must be loaded to use this driver.');

		ini_set('memcache.allow_failover', (isset($config['allow_failover']) AND $config['allow_failover']) ? YES : NO);

		$this->config = $config;
		$this->backend = new Memcache;

		$this->flags = (isset($config['compression']) AND $config['compression']) ? MEMCACHE_COMPRESSED : NO;

		foreach($config['servers'] as $server) {
			// Make sure all required keys are set
			$server += array('host' => '127.0.0.1',
			                 'port' => 11211,
			                 'persistent' => NO,
			                 'weight' => 1,
			                 'timeout' => 1,
			                 'retry_interval' => 15
			);

			// Add the server to the pool
			$this->backend->addServer($server['host'], $server['port'], (bool) $server['persistent'], (int) $server['weight'], (int) $server['timeout'], (int) $server['retry_interval'], YES, array($this,'_memcache_failure_callback'));
		}
	}

	public function _memcache_failure_callback($host, $port) {
		$this->backend->setServerParams($host, $port, 1, -1, NO);
		Eight::log('error', __('Cache: Memcache server down: :host:::port:',array(':host:' => $host,':port:' => $port)));
	}

	public function set($items, $tags = NULL, $lifetime = NULL) {
		if($lifetime !== 0) {
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}

		if($tags !== NULL)
			throw new Cache_Exception('Memcache driver does not support tags');

		foreach($items as $key => $value) {
			if(is_resource($value))
				throw new Cache_Exception('Caching of resources is impossible, because resources cannot be serialised.');

			if(!$this->backend->set($key, $value, $this->flags, $lifetime)) {
				return NO;
			}
		}

		return YES;
	}

	public function get($keys, $single = NO) {
		$items = $this->backend->get($keys);

		if($single) {
			if($items === NO)
			    return NULL;

			return (count($items) > 0) ? current($items) : NULL;
		} else {
			return ($items === NO) ? array() : $items;
		}
	}
	
	public function increment() {
		throw new Cache_Exception('Memcache driver does not support increment.');
	}
	
	public function decrement() {
		throw new Cache_Exception('Memcache driver does not support decrement.');
	}

	/**
	 * Get cache items by tag
	 */
	public function get_tag($tags) {
		throw new Cache_Exception('Memcache driver does not support tags');
	}

	/**
	 * Delete cache item by key
	 */
	public function delete($keys) {
		foreach($keys as $key) {
			if(!$this->backend->delete($key)) {
				return NO;
			}
		}

		return YES;
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags) {
		throw new Cache_Exception('Memcache driver does not support tags');
	}

	/**
	 * Empty the cache
	 */
	public function delete_all() {
		return $this->backend->flush();
	}
} // End Cache Memcache Driver

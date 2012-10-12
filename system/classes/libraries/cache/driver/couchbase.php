<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Couchbase Cache driver.
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Cache_Driver_Couchbase extends Cache_Driver {
	
	protected $config;
	protected $backend;
	protected $flags;

	public function __construct($config) {
		if(!extension_loaded('couchbase')) {
			throw new Cache_Exception('The Couchbase PHP extension must be loaded to use this driver.');
		}

		$this->config = $config;
		
		// Ensure the config file defines the server nodes properly
		if(isset($config['servers']) && is_array($config['servers']) && count($config['servers']) >= 1) {
			$server = array_pop($config['servers']);
			while($this->connect($server) === FALSE) {
				if(!is_array($config['servers']) || count($config['servers']) == 0) {
					throw new Cache_Exception('Could not find a Couchbase server to connect to.');
				}
				$server = array_pop($config['servers']);
			}
		} else {
			throw new Cache_Exception('No Couchbase servers have been defined. Check your config.');
		}
	}
	
	private function connect($server) {
		try {
			$this->backend = new Couchbase($server['host'].':'.$server['port'], $this->config['username'], $this->config['password'], $this->config['bucket'], $this->config['persistent']);
		} catch(Exception $e) {
			return FALSE;
		}
		
		return TRUE;
	}

	public function set($items, $tags = NULL, $lifetime = NULL) {
		if($tags !== NULL) {
			throw new Cache_Exception('Couchbase driver does not support tags.');
		}
		
		foreach($items as $key => $value) {
			if(is_resource($value)) {
				throw new Cache_Exception('Caching of resources is impossible, because resources cannot be serialised. Key of resource is: '.$key);
			}
		}
		
		if(!$this->backend->setMulti($items, $lifetime)) {
			return FALSE;
		}

		return TRUE;
	}

	public function get($keys, $single = TRUE) {
		$items = $this->backend->getMulti($keys);
		
		if($single) {
			if(!is_array($items)) {
				return NULL;
			}
			return (count($items) > 0) ? current($items) : NULL;
		} else {
			return (!is_array($items)) ? array() : $items;
		}
	}
	
	public function increment($keys, $step = 1) {
		foreach($keys as $key) {
			if(!$this->backend->increment($key, $step)) {
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	public function decrement($keys, $step = 1) {
		foreach($keys as $key) {
			if(!$this->backend->decrement($key, $step)) {
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Get cache items by tag
	 */
	public function get_tag($tags) {
		throw new Cache_Exception('Couchbase driver does not support tags');
	}

	/**
	 * Delete cache item by key
	 */
	public function delete($keys) {
		foreach($keys as $key) {
			if(!$this->backend->delete($key)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags) {
		throw new Cache_Exception('Couchbase driver does not support tags.');
	}

	/**
	 * Empty the cache
	 */
	public function delete_all() {
		return $this->backend->flush();
	}
	
} // End Cache Couchbase Driver
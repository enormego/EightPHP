<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * XCache-based Cache driver.
 * 
 * @version		$Id: xcache.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 * @TODO		Check if XCache cleans its own keys.
 */
class Cache_Driver_Xcache extends Cache_Driver {
	protected $config;

	public function __construct($config) {
		if(!extension_loaded('xcache'))
			throw new Cache_Exception('The Xcache PHP extension must be loaded to use this driver.');

		$this->config = $config;
	}

	public function set($items, $tags = NULL, $lifetime = NULL) {
		if($tags !== NULL) {
			Eight::log('debug', __('Cache: XCache driver does not support tags'));
		}

		foreach($items as $key => $value) {
			if(is_resource($value))
				throw new Cache_Exception('Caching of resources is impossible, because resources cannot be serialised.');

			if(!xcache_set($key, $value, $lifetime)) {
				return NO;
			}
		}

		return YES;
	}

	public function get($keys, $single = NO) {
		$items = array();

		foreach($keys as $key) {
			if(xcache_isset($id)) {
				$items[$key] = xcache_get($id);
			} else {
				$items[$key] = NULL;
			}
		}

		if($single) {
			return ($items === NO OR count($items) > 0) ? current($items) : NULL;
		} else {
			return ($items === NO) ? array() : $items;
		}
	}

	/**
	 * Get cache items by tag
	 */
	public function get_tag($tags) {
		Eight::log('debug', __('Cache: XCache driver does not support tags'));
		return NULL;
	}

	/**
	 * Delete cache item by key
	 */
	public function delete($keys) {
		foreach($keys as $key) {
			if(!xcache_unset($key)) {
				return NO;
			}
		}

		return YES;
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags) {
		Eight::log('debug', __('Cache: XCache driver does not support tags'));
		return NULL;
	}

	/**
	 * Empty the cache
	 */
	public function delete_all() {
		$this->auth();
		$result = YES;

		for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++) {
			if(xcache_clear_cache(XC_TYPE_VAR, $i) !== NULL) {
				$result = NO;
				break;
			}
		}

		// Undo the login
		$this->auth(YES);

		return $result;
	}

	private function auth($reverse = NO) {
		static $backup = array();

		$keys = array('PHP_AUTH_USER', 'PHP_AUTH_PW');

		foreach($keys as $key) {
			if($reverse) {
				if(isset($backup[$key])) {
					$_SERVER[$key] = $backup[$key];
					unset($backup[$key]);
				} else {
					unset($_SERVER[$key]);
				}
			} else {
				$value = getenv($key);

				if(!empty($value)) {
					$backup[$key] = $value;
				}

				$_SERVER[$key] = $this->config->{$key};
			}
		}
	}
} // End Cache XCache Driver

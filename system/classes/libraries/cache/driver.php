<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cache driver abstract class.
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
abstract class Cache_Driver {
	/**
	 * Set cache items  
	 */
	abstract public function set($items, $tags = NULL, $lifetime = NULL);

	/**
	 * Get a cache items by key 
	 */
	abstract public function get($keys, $single = NO);

	/**
	 * Increment cache items by a step value
	 */
	abstract public function increment($keys, $step = 1);
	
	/**
	 * Decrement cache items by a step value
	 */
	abstract public function decrement($keys, $step = 1);

	/**
	 * Get cache items by tag 
	 */
	abstract public function get_tag($tags);

	/**
	 * Delete cache item by key 
	 */
	abstract public function delete($keys);

	/**
	 * Delete cache items by tag 
	 */
	abstract public function delete_tag($tags);

	/**
	 * Empty the cache
	 */
	abstract public function delete_all();
} // End Cache Driver
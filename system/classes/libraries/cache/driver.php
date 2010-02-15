<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cache driver abstract class.
 *
 * @version		$Id: driver.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * File-based Cache driver.
 *
 * @package		System
 * @subpackage	Libraries.Cache
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Cache_Driver_File extends Cache_Driver {
	protected $config;
	protected $backend;

	public function __construct($config) {
		$this->config = $config;
		$this->config['directory'] = str_replace('\\', '/', realpath($this->config['directory'])).'/';

		if(!is_dir($this->config['directory']) OR!is_writable($this->config['directory']))
			throw new Cache_Exception('The configured cache directory, :directory:, is not writable.', array(':directory:' => $this->config['directory']));
	}

	/**
	 * Finds an array of files matching the given id or tag.
	 *
	 * @param  string  cache key or tag
	 * @param  bool    search for tags
	 * @return array   of filenames matching the id or tag
	 */
	public function exists($keys, $tag = NO) {
		if($keys === YES) {
			// Find all the files
			return glob($this->config['directory'].'*~*~*');
		} elseif($tag === YES) {
			// Find all the files that have the tag name
			$paths = array();
			
			foreach( (array) $keys as $tag) {
				$paths = array_merge($paths, glob($this->config['directory'].'*~*'.$tag.'*~*'));
			}

			// Find all tags matching the given tag
			$files = array();

			foreach($paths as $path) {
				// Split the files
				$tags = explode('~', basename($path));

				// Find valid tags
				if(count($tags) !== 3 OR empty($tags[1]))
					continue;

				// Split the tags by plus signs, used to separate tags
				$item_tags = explode('+', $tags[1]);

				// Check each supplied tag, and match aginst the tags on each item.
				foreach($keys as $tag) {
					if(in_array($tag, $item_tags)) {
						// Add the file to the array, it has the requested tag
						$files[] = $path;
					}
				}
			}

			return $files;
		} else {
			$paths = array();

			foreach( (array) $keys as $key) {
				// Find the file matching the given key
				$paths = array_merge($paths, arr::c(glob($this->config['directory'].str_replace(array('/', '\\', ' '), '_', $key).'~*')));
			}

			return $paths;
		}
	}

	public function set($items, $tags = NULL, $lifetime = NULL) {
		if($lifetime !== 0) {
			// File driver expects unix timestamp
			$lifetime += time();
		}


		if(!is_null($tags) AND!empty($tags)) {
			// Convert the tags into a string list
			$tags = implode('+', (array) $tags);
		}

		$success = YES;

		foreach($items as $key => $value) {
			if(is_resource($value))
				throw new Cache_Exception('Caching of resources is impossible, because resources cannot be serialised.');

			// Remove old cache file
			$this->delete($key);

			if(!(bool) file_put_contents($this->config['directory'].str_replace(array('/', '\\', ' '), '_', $key).'~'.$tags.'~'.$lifetime, serialize($value))) {
				$success = NO;
			}
		}

		return $success;
	}

	public function get($keys, $single = NO) {
		$items = array();

		if($files = $this->exists($keys)) {
			// Turn off errors while reading the files
			$ER = error_reporting(0);

			foreach($files as $file) {
				// Validate that the item has not expired
				if($this->expired($file)) {
					// If it expired, let's go ahead and delete it.
					@unlink($file);
					continue;
				}

				list($key, $junk) = explode('~', basename($file), 2);

				if(($data = file_get_contents($file)) !== NO) {
					// Unserialize the data
					$data = unserialize($data);
				} else {
					$data = NULL;
				}

				$items[$key] = $data;
			}

			// Turn errors back on
			error_reporting($ER);
		}

		// Reutrn a single item if only one key was requested
		if($single) {
			return (count($items) > 0) ? current($items) : NULL;
		} else {
			return $items;
		}
	}

	/**
	 * Get cache items by tag
	 */
	public function get_tag($tags) {
		// An array will always be returned
		$result = array();

		if($paths = $this->exists($tags, YES)) {
			// Find all the files with the given tag
			foreach($paths as $path) {
				// Get the id from the filename
				list($key, $junk) = explode('~', basename($path), 2);

				if(($data = $this->get($key, YES)) !== NO) {
					// Add the result to the array
					$result[$key] = $data;
				}
			}
		}

		return $result;
	}

	/**
	 * Delete cache items by keys or tags
	 */
	public function delete($keys, $tag = NO) {
		$success = YES;

		$paths = $this->exists($keys, $tag);

		// Disable all error reporting while deleting
		$ER = error_reporting(0);

		foreach($paths as $path) {
			// Remove the cache file
			if(!unlink($path)) {
				Eight::log('error', 'Cache: Unable to delete cache file: '.$path);
				$success = NO;
			}
		}

		// Turn on error reporting again
		error_reporting($ER);

		return $success;
	}

	/**
	 * Delete cache items by tag
	 */
	public function delete_tag($tags) {
		return $this->delete($tags, YES);
	}

	/**
	 * Empty the cache
	 */
	public function delete_all() {
		return $this->delete(YES);
	}

	/**
	 * Check if a cache file has expired by filename.
	 *
	 * @param  string|array   array of filenames
	 * @return bool
	 */
	protected function expired($file) {
		// Get the expiration time
		$expires = (int) substr($file, strrpos($file, '~') + 1);

		// Expirations of 0 are "never expire"
		return ($expires !== 0 AND $expires <= time());
	}
} // End Cache File Driver

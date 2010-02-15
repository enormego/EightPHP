<?php
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the [mcrypt extension](http://php.net/mcrypt).
 *
 * @version		$Id: encrypt.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Encrypt_Core {

	// OS-dependant RAND type to use
	protected static $rand;

	// Configuration
	protected $config;

	/**
	 * Returns a singleton instance of Encrypt.
	 *
	 * @param   array  configuration options
	 * @return  Encrypt_Core
	 */
	public static function instance($config = nil) {
		static $instance;

		// Create the singleton
		empty($instance) and $instance = new Encrypt((array) $config);

		return $instance;
	}

	/**
	 * Loads encryption configuration and validates the data.
	 *
	 * @param   array|string      custom configuration or config group name
	 * @throws  Eight_Exception
	 */
	public function __construct($config = NO) {
		if(!defined('MCRYPT_ENCRYPT'))
			throw new Eight_Exception('encrypt.requires_mcrypt');

		if(is_string($config)) {
			$name = $config;

			// Test the config group name
			if(($config = Eight::config('encryption.'.$config)) === nil)
				throw new Eight_Exception('encrypt.undefined_group', $name);
		}

		if(is_array($config)) {
			// Append the default configuration options
			$config += Eight::config('encryption.default');
		} else {
			// Load the default group
			$config = Eight::config('encryption.default');
		}

		if(empty($config['key']))
			throw new Eight_Exception('encrypt.no_encryption_key');

		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($config['cipher'], $config['mode']);

		if(strlen($config['key']) > $size) {
			// Shorten the key to the maximum size
			$config['key'] = substr($config['key'], 0, $size);
		}

		// Find the initialization vector size
		$config['iv_size'] = mcrypt_get_iv_size($config['cipher'], $config['mode']);

		// Cache the config in the object
		$this->config = $config;

		Eight::log('debug', 'Encrypt Library initialized');
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 * @param   string  data to be encrypted
	 * @param   string  salt to use with key, if NULL not added
	 * @return  string  encrypted data
	 */
	public function encode($data, $salt=NULL) {
		// Set the rand type if it has not already been set
		if(self::$rand === nil) {
			if(EIGHT_IS_WIN) {
				// Windows only supports the system random number generator
				self::$rand = MCRYPT_RAND;
			} else {
				if(defined('MCRYPT_DEV_URANDOM')) {
					// Use /dev/urandom
					self::$rand = MCRYPT_DEV_URANDOM;
				} elseif(defined('MCRYPT_DEV_RANDOM')) {
					// Use /dev/random
					self::$rand = MCRYPT_DEV_RANDOM;
				} else {
					// Use the system random number generator
					self::$rand = MCRYPT_RAND;
				}
			}
		}

		if(self::$rand === MCRYPT_RAND) {
			// The system random number generator must always be seeded each
			// time it is used, or it will not produce YES random results
			mt_srand();
		}

		// Fetch the salted key
		$key = $this->salted_key($salt);

		// Create a random initialization vector of the proper size for the current cipher
		$iv = mcrypt_create_iv($this->config['iv_size'], self::$rand);

		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->config['cipher'], $key, $data, $this->config['mode'], $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$data);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 * @param   string  encoded string to be decrypted
	 * @param   string  salt to use with key, if NULL not added
	 * @return  string  decrypted data
	 */
	public function decode($data, $salt=NULL) {
		// Convert the data back to binary
		$data = base64_decode($data);

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->config['iv_size']);

		// Remove the iv from the data
		$data = substr($data, $this->config['iv_size']);
		
		// Fetch the salted key
		$key = $this->salted_key($salt);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->config['cipher'], $key, $data, $this->config['mode'], $iv), "\0");
	}
	
	/**
	 * Returns the salted key, if necessary
	 *
	 * @param   string  salt to use with key
	 * @return  string  salted key, if salt provided, standard key otherwise
	 */
	private function salted_key($salt=NULL) {
		$key = $this->config['key'];
		
		if(is_null($salt) || !is_string($salt) || str::e($salt)) return $key;
		
		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($this->config['cipher'], $this->config['mode']);
		
		// If salt is too big, we'll just use the salt as the key, since we can't salt the key
		if(strlen($salt) >= $size) {
			return substr($salt, 0, $size);
		}

		// If the salt+key is too long, we'll pad the beginning of the salt with as much of the key as possible
		if(strlen($key) + strlen($salt) > $size) {
			return substr($key, 0, $size-strlen($salt)).$salt;
		} 
		
		// Append the salt to the key and return
		return $key.$salt;
	}

} // End Encrypt

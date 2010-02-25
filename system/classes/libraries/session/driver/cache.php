<?php
/**
 * Session cache driver.
 *
 * Cache library config goes in the session.storage config entry:
 * $config['storage'] = array(
 *     'driver' => 'apc',
 *     'requests' => 10000
 * );
 * Lifetime does not need to be set as it is
 * overridden by the session expiration setting.
 *
 * @package		System
 * @subpackage	Libraries.Sessions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Session_Driver_Cache_Core implements Session_Driver {

	protected $cache;
	protected $encrypt;

	public function __construct() {
		// Load Encrypt library
		if(Eight::config('session.encryption')) {
			$this->encrypt = new Encrypt;
		}

		Eight::log('debug', 'Session Cache Driver Initialized');
	}

	public function open($path, $name) {
		$config = Eight::config('session.storage');

		if(empty($config)) {
			// Load the default group
			$config = Eight::config('cache.default');
		} elseif(is_string($config)) {
			$name = $config;

			// Test the config group name
			if(($config = Eight::config('cache.'.$config)) === nil)
				throw new Eight_Exception('cache.undefined_group', $name);
		}

		$config['lifetime'] = (Eight::config('session.expiration') == 0) ? 86400 : Eight::config('session.expiration');
		$this->cache = new Cache($config);

		return is_object($this->cache);
	}

	public function close() {
		return YES;
	}
	
	public function identify() {
		return session_id();
	}

	public function read($id) {
		$id = 'session_'.$id;
		if($data = $this->cache->get($id)) {
			return Eight::config('session.encryption') ? $this->encrypt->decode($data) : $data;
		}

		// Return value must be string, NOT a boolean
		return '';
	}

	public function write($id, $data) {
		$id = 'session_'.$id;
		$data = Eight::config('session.encryption') ? $this->encrypt->encode($data) : $data;

		return $this->cache->set($id, $data);
	}

	public function destroy($id) {
		$id = 'session_'.$id;
		return $this->cache->delete($id);
	}

	public function regenerate() {
		session_regenerate_id(YES);

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime) {
		// Just return, caches are automatically cleaned up
		return YES;
	}

} // End Session Cache Driver

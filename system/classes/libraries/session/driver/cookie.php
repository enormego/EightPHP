<?php
/**
 * Session cookie driver.
 *
 * @package		System
 * @subpackage	Libraries.Sessions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Session_Driver_Cookie_Core implements Session_Driver {

	protected $cookie_name;
	protected $encrypt; // Library

	public function __construct() {
		$this->cookie_name = Eight::config('session.name').'_data';

		if(Eight::config('session.encryption')) {
			$this->encrypt = Encrypt::instance();
		}

		Eight::log('debug', 'Session Cookie Driver Initialized');
	}

	public function open($path, $name) {
		return YES;
	}

	public function close() {
		return YES;
	}

	public function read($id) {
		$data = (string) cookie::get($this->cookie_name);

		if($data == '')
			return $data;

		return empty($this->encrypt) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data) {
		$data = empty($this->encrypt) ? base64_encode($data) : $this->encrypt->encode($data);

		if(strlen($data) > 4048) {
			Eight::log('error', 'Session ('.$id.') data exceeds the 4KB limit, ignoring write.');
			return NO;
		}

		return cookie::set($this->cookie_name, $data, Eight::config('session.expiration'));
	}

	public function destroy($id) {
		return cookie::delete($this->cookie_name);
	}

	public function regenerate() {
		session_regenerate_id(YES);

		// Return new id
		return session_id();
	}

	public function gc($maxlifetime) {
		return YES;
	}

} // End Session Cookie Driver Class
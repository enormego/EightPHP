<?php
/**
 * Cookie helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class cookie_Core {

	/**
	 * Sets a cookie with the given parameters.
	 *
	 * @param   string   cookie name or array of config options
	 * @param   string   cookie value
	 * @param   integer  number of seconds before the cookie expires
	 * @param   string   URL path to allow
	 * @param   string   URL domain to allow
	 * @param   boolean  HTTPS only
	 * @param   boolean  HTTP only (requires PHP 5.2 or higher)
	 * @return  boolean
	 */
	public static function set($name, $value = nil, $expire = nil, $path = nil, $domain = nil, $secure = nil, $httponly = nil) {
		if(headers_sent())
			return NO;

		// If the name param is an array, we import it
		is_array($name) and extract($name, EXTR_OVERWRITE);

		// Fetch default options
		$config = Eight::config('cookie');

		foreach(array('value', 'expire', 'domain', 'path', 'secure', 'httponly') as $item) {
			if($$item === nil and isset($config[$item])) {
				$$item = $config[$item];
			}
		}

		// Expiration timestamp
		$expire = ($expire == 0) ? 0 : time() + (int) $expire;

		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Fetch a cookie value, using the Input library.
	 *
	 * @param   string   cookie name
	 * @param   mixed    default value
	 * @param   boolean  use XSS cleaning on the value
	 * @return  string
	 */
	public static function get($name, $default = nil, $xss_clean = NO) {
		return Input::instance()->cookie($name, $default, $xss_clean);
	}

	/**
	 * Nullify and unset a cookie.
	 *
	 * @param   string   cookie name
	 * @param   string   URL path
	 * @param   string   URL domain
	 * @return  boolean
	 */
	public static function delete($name, $path = nil, $domain = nil) {
		if(!isset($_COOKIE[$name]))
			return NO;

		// Delete the cookie from globals
		unset($_COOKIE[$name]);

		// Sets the cookie value to an empty string, and the expiration to 24 hours ago
		return cookie::set($name, '', -86400, $path, $domain, NO, NO);
	}

} // End cookie
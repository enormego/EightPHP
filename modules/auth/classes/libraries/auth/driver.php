<?php
/**
 * Auth driver interface.
 *
 * @version		$Id: driver.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Authentication
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
 
interface Auth_Driver {

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($username, $password, $remember);

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($username);

	/**
	 * Logs a user in, based on stored credentials, typically cookies.
	 *
	 * @return  boolean
	 */
	public function auto_login();

	/**
	 * Log a user out.
	 *
	 * @param   boolean  completely destroy the session
	 * @return  boolean
	 */
	public function logout($destroy);

	/**
	 * Checks if a session is active.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role);

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($username);

} // End Auth_Driver Interface
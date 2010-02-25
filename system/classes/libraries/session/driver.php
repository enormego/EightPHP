<?php
/**
 * Session driver interface
 *
 * @package		System
 * @subpackage	Libraries.Sessions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
interface Session_Driver {

	/**
	 * Opens a session.
	 *
	 * @param   string   save path
	 * @param   string   session name
	 * @return  boolean
	 */
	public function open($path, $name);

	/**
	 * Closes a session.
	 *
	 * @return  boolean
	 */
	public function close();
	
	/**
	 * Sets a session id.
	 *
	 * @return  session ID
	 */
	public function identify();
	
	/**
	 * Reads a session.
	 *
	 * @param   string  session id
	 * @return  string
	 */
	public function read($id);

	/**
	 * Writes a session.
	 *
	 * @param   string   session id
	 * @param   string   session data
	 * @return  boolean
	 */
	public function write($id, $data);

	/**
	 * Destroys a session.
	 *
	 * @param   string   session id
	 * @return  boolean
	 */
	public function destroy($id);

	/**
	 * Regenerates the session id.
	 *
	 * @return  string
	 */
	public function regenerate();

	/**
	 * Garbage collection.
	 *
	 * @param   integer  session expiration period
	 * @return  boolean
	 */
	public function gc($maxlifetime);

} // End Session Driver Interface
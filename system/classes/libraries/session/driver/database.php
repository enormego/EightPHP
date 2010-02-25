<?php
/**
 * Session database driver.
 *
 * @package		System
 * @subpackage	Libraries.Sessions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Session_Driver_Database_Core implements Session_Driver {

	/*
		--
		-- Table structure for table `sessions`
		--

		CREATE TABLE IF NOT EXISTS `sessions` (
		  `session_id` varchar(40) collate latin1_general_ci NOT NULL default '0',
		  `session_ip` varchar(16) collate latin1_general_ci NOT NULL default '0',
		  `session_user_agent` varchar(150) collate latin1_general_ci NOT NULL,
		  `session_last_activity` int(10) unsigned NOT NULL default '0',
		  `session_data` blob NOT NULL,
		  PRIMARY KEY  (`session_id`)
		)
		
		
	*/

	// Database settings
	protected $db = 'default';
	protected $table = 'sessions';

	// Encryption
	protected $encrypt;

	// Session settings
	protected $session_id;
	protected $written = FALSE;

	public function __construct()
	{
		// Load configuration
		$config = Eight::config('session');

		if ( ! empty($config['encryption']))
		{
			// Load encryption
			$this->encrypt = new Encrypt;
		}

		if (is_array($config['storage']))
		{
			if ( ! empty($config['storage']['group']))
			{
				// Set the group name
				$this->db = $config['storage']['group'];
			}

			if ( ! empty($config['storage']['table']))
			{
				// Set the table name
				$this->table = $config['storage']['table'];
			}
		}

		// Load database
		$this->db = Database::instance($this->db);
		
		// Force PHP to write the session on shutdown.
		register_shutdown_function('session_write_close'); 
		
		Eight::log('debug', 'Session Database Driver Initialized');
	}

	public function open($path, $name) {
		return TRUE;
	}

	public function close() {
		return TRUE;
	}
	
	public function identify() {
		return session_id();
	}
	
	public function read($id) {
		// Load the session
		$this->db->use_master(YES);
		$query = $this->db->from($this->table)->where('session_id', $id)->limit(1)->get()->result(TRUE);

		if ($query->count() === 0)
		{
			// No current session
			$this->session_id = NULL;

			return '';
		}

		// Set the current session id
		$this->session_id = $id;

		// Load the data
		$data = $query->current()->session_data;

		return ($this->encrypt === NULL) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data) {
		// Only write once...
		if($this->written) {
			return true;
		}
		
		$data = array
		(
			'session_id' 			=> $id,
			'session_ip'			=>	$_SERVER['REMOTE_ADDR'],
			'session_user_agent'	=>	$_SERVER['HTTP_USER_AGENT'],
			'session_last_activity' => time(),
			'session_data' 			=> ($this->encrypt === NULL) ? base64_encode($data) : $this->encrypt->encode($data)
		);
		
		if ($this->session_id === NULL)
		{
			// Insert a new session
			$this->db->use_master(YES);
			$query = $this->db->insert($this->table, $data); 
		}
		elseif ($id === $this->session_id)
		{
			// Do not update the session_id
			unset($data['session_id']);

			// Update the existing session
			$this->db->use_master(YES);
			$query = $this->db->update($this->table, $data, array('session_id' => $id));
		}
		else
		{
			// Update the session and id
			$this->db->use_master(YES);
			$query = $this->db->update($this->table, $data, array('session_id' => $this->session_id));
			
			// Set the new session id
			$this->session_id = $id;
		}
		
		// Written!
		$this->written = true;
		
		return (bool) $query->count();
	}

	public function destroy($id) {
		// Delete the requested session
		$this->db->use_master(YES);
		$this->db->delete($this->table, array('session_id' => $id));

		// Session id is no longer valid
		$this->session_id = NULL;

		return TRUE;
	}

	public function regenerate($delete_old_session) {
		// Generate a new session id
		session_regenerate_id($delete_old_session);

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime) {
		// Delete all expired sessions
		$this->db->use_master(YES);
		$query = $this->db->delete($this->table, array('session_last_activity <' => time() - $maxlifetime));

		Eight::log('debug', 'Session garbage collected: '.$query->count().' row(s) deleted.');

		return TRUE;
	}

} // End Session Database Driver
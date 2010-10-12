<?php
/**
 * CMD Line helper class.
 * 
 * 		NOTE: This helper is not compatible with Windows.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class cli_Core {

	/**
	 * Finds out how many of the current script are running
	 */
	public static function how_many_of_me() {
		// Get the launch command
		if(!$cmd = self::launch_cmd()) return FALSE;
		
		// Find all the processes with the same args
		$procs = shell_exec('ps -A -o pid,args | grep "'.$cmd.'$"');
		$procs = explode("\n", $procs);
		if(is_array($procs)) {
			foreach($procs as $k=>$v) {
				if(empty($v)) unset($procs[$k]);
			}
			return count($procs);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Finds the CMD + Args used to launch the current process
	 */
	public static function launch_cmd() {
		// Find out my PID
		$pid = getmypid();

		// Find the args used to run me
		$procs = shell_exec('ps -A -o pid,args | grep "^'.$pid.' "');
		$procs = explode("\n", $procs, -1);
		$current_cmd = explode(' ', $procs[0], 2);
		if(is_array($current_cmd)) {
			return $current_cmd[1];
		} else {
			return FALSE;
		}
	}

} // End cli Helper
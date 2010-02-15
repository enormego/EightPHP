<?php
/**
 * File System API driver
 *
 * @version		$Id: driver.php 17 2008-09-19 12:45:38Z shaun $
 *
 * @package		Modules
 * @subpackage	FileSystem
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
abstract class FS_Driver {

	/**
	 * Check to see if a file exists.
	 *
	 * @param  string  Path to the file or directory
	 * @return BOOL
	 */
	abstract public function file_exists($pathname="");

	/**
	 * Change current working directory.
	 *
	 * @param  string  Path to the directory
	 */
	abstract public function chdir($pathname);

	/**
	 * Create a directory.
	 *
	 * @param  string  Path of the directory to create
	 * @param  integer Mode of the directory
	 * @param  BOOL    Create parent directories if they do not exist
	 */
	abstract public function mkdir($pathname, $mode=0777, $recursive=false);

	/**
	 * Change the mode of a file or directory.
	 *
	 * @param  string  Path to the file or directory
	 * @param  integer Mode to change file or directory to
	 */
	abstract public function chmod($pathname, $mode);
	
	/**
	 * Change the mode of a file or directory.
	 *
	 * @param  string  Path to the file or directory
	 * @param  integer Mode to change file or directory to
	 */
	abstract public function copy($source_pathname, $destination_pathname, $mime=NULL, $filename=NULL, $extras=array());
	
	/**
	 * Remove file.
	 *
	 * @param  string  Path to file
	 */
	abstract public function unlink($pathname);
	
	/**
	 * Remove directory.
	 *
	 * @param  string  Path to directory
	 */
	abstract public function rmdir($pathname);

	/**
	 * Execute a command on FS.
	 *
	 * @param  string  Path to the file or directory
	 * @return mixed
	 */
	abstract public function exec($command="");
}
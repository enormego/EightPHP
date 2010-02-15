<?php

/**
 * FS is an abstract FileSystem that implements most native FS PHP methods
 * around different drivers like regular native FS, Amazon S3, and SSH.
 *
 * @package		Modules
 * @subpackage	FileSystem
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class FS_Core {
	private $driver = NULL;
	private $config = array();
	
	public function __construct($config=array()) {
		if(!is_array($config)) {
			throw new Eight_Exception('fs.invalid_configuation');
		} else {
			$this->config = $config;
		}
		
		$driver_class = 'FS_Driver_'.$this->config['driver'];

		if(!class_exists($driver_class)) {
			throw new Eight_Exception('fs.invalid_driver');
		}
		
		$this->driver = new $driver_class($this->config['info']);
	}
	
	/**
	 * Check to see if a file exists.
	 *
	 * @param  string  Path to the file or directory
	 * @return BOOL
	 */
	public function file_exists($pathname="") {
		return $this->driver->file_exists($pathname);
	}

	/**
	 * Change current working directory.
	 *
	 * @param  string  Path to the directory
	 */
	public function chdir($pathname) {
		return $this->driver->chdir($pathname);
	}

	/**
	 * Create a directory.
	 *
	 * @param  string  Path of the directory to create
	 * @param  integer Mode of the directory
	 * @param  BOOL    Create parent directories if they do not exist
	 */
	public function mkdir($pathname, $mode=777, $recursive=false) {
		return $this->driver->mkdir($pathname, $mode, $recursive);
	}

	/**
	 * Change the mode of a file or directory.
	 *
	 * @param  string  Path to the file or directory
	 * @param  integer Mode to change file or directory to
	 */
	public function chmod($pathname, $mode) {
		return $this->driver->chmod($pathname, $mode);
	}
	
	/**
	 * Change the mode of a file or directory.
	 *
	 * @param  string  Path to the file or directory
	 * @param  integer Mode to change file or directory to
	 */
	public function copy($source_pathname, $destination_pathname, $mime=NULL, $filename=NULL, $extras=array()) {
		return $this->driver->copy($source_pathname, $destination_pathname, $mime, $filename, $extras);
	}
	
	/**
	 * Remove file.
	 *
	 * @param  string  Path to file
	 */
	public function unlink($pathname) {
		return $this->driver->unlink($pathname);
	}
	
	/**
	 * Remove directory.
	 *
	 * @param  string  Path to directory
	 */
	public function rmdir($pathname) {
		return $this->driver->rmdir($pathname);
	}

	/**
	 * Execute a command on FS.
	 *
	 * @param  string  Path to the file or directory
	 * @return mixed
	 */
	public function exec($command="") {
		return $this->driver->exec($command);
	}
	
}
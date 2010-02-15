<?php

/**
 * Native Driver for the FS_Core class.
 *
 * @package		Modules
 * @subpackage	FileSystem
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class FS_Driver_Native extends FS_Driver {
	private $config = array();
	private $cwd = null;
	
	public function __construct($config=array()) {
		$this->config = $config;
				
		if($config['path']) {
			$this->chdir($config['path']);
		} else {
			$this->cwd = rtrim(getcwd(),"/")."/";
		}
	}
	
	public function exec($command="") {
		$this->push_cwd();
		$output = shell_exec($command);
		$this->pop_cwd();
		return trim($output);
	}
	
	public function file_exists($pathname="") {
		$this->push_cwd();
		$file_exists = file_exists($pathname);
		$this->pop_cwd();
		return $file_exists;
	}
	
	public function chdir($pathname) {
		$this->push_cwd();
		chdir($pathname);
		$this->cwd = getcwd();
		$this->cwd = rtrim($pathname,"/")."/";
		$this->pop_cwd();
	}
	
	public function mkdir($pathname, $mode=0777, $recursive=false) {
		$this->push_cwd();
		mkdir($pathname, $mode, $recursive);
		$this->pop_cwd();
	}
	
	public function chmod($pathname, $mode) {
		$this->push_cwd();
		chmod($pathname, $mode);
		$this->pop_cwd();
	}
	
	public function copy($source_pathname, $destination_pathname, $mime=NULL, $filename=NULL, $extras=array()) {
		$this->push_cwd();
		
		if($destination_pathname{0} != '/') {
			$destination_pathname = $this->cwd . $destination_pathname;
		}
		
		copy($source_pathname, $destination_pathname);
		$this->pop_cwd();
	}
	
	public function unlink($pathname) {
		$this->push_cwd();
		unlink($pathname);
		$this->pop_cwd();
	}
	
	public function rmdir($pathname) {
		$this->push_cwd();
		rmdir($pathname);
		$this->pop_cwd();
	}
	
	private function push_cwd() {
		$this->pop_dir = getcwd();
		if(strlen($this->cwd) > 0) 
			chdir($this->cwd);
	}
	
	private function pop_cwd() {
		chdir($this->pop_dir);
	}
}
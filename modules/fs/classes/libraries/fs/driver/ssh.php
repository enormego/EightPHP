<?php

dl('ssh2.so');

/**
 * SSH Driver for the FS_Core class.
 * @note Requires the SSH2 PECL
 *
 * @package		Modules
 * @subpackage	FileSystem
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */

class FS_Driver_SSH extends FS_Driver {
	private $config = array();
	private $session = null;
	private $shell = null;
	private $cwd = null;
	
	public function __construct($config=array()) {
		if(count($config) == 0 || $config['login']['host'] == '') return;
		$this->config = $config;
		
		$this->session = ssh2_connect($this->config['login']['host']);
		$this->session = ssh2_connect($this->config['login']['host']);
		ssh2_auth_password($this->session, $this->config['login']['user'], $this->config['login']['pass']);
		
		if($config['path']) {
			$this->chdir($config['path']);
		} else {
			$this->cwd = $this->exec('pwd');
		}
		
		$this->cwd = rtrim($this->cwd,"/")."/";
	}
	
	public function exec($command="") {
		if($this->cwd && strlen($this->cwd) > 0) {
			$command = 'cd "'.$this->cwd.'"; ' . $command;
		}

		$stream = ssh2_exec($this->session, $command);
		stream_set_blocking($stream, true);
		
		// The command may not finish properly if the stream is not read to end
		$output = stream_get_contents($stream);
		
		return trim($output);
	}
	
	public function file_exists($pathname="") {
		$output = $this->exec('ls "'.$pathname.'" 2>/dev/null');
		return strlen($output) > 0 ? true : false;
	}
	
	public function chdir($pathname) {
		$pathname = $this->exec('cd "'.$pathname.'"; pwd');
		$this->cwd = rtrim($pathname,"/")."/";
	}
	
	public function mkdir($pathname, $mode=0777, $recursive=false) {
		if($recursive) {
			$this->exec('mkdir -p "'.$pathname.'"');
		} else {
			$this->exec('mkdir "'.$pathname.'"');
		}
		
		$this->chmod($pathname, $mode);
	}
	
	public function chmod($pathname, $mode) {
		$this->exec('chmod '.$mode.' "'.$pathname.'"');
	}
	
	public function copy($source_pathname, $destination_pathname, $mime=NULL, $filename=NULL, $extras=array()) {
		if(count($source_pathname) == 0 || count($destination_pathname) == 0) return;
		
		if($destination_pathname{0} != '/') {
			$destination_pathname = $this->cwd . $destination_pathname;
		}
		
		ssh2_scp_send($this->session, $source_pathname, $destination_pathname);
	}
	
	public function unlink($pathname) {
		$this->exec("rm -f '".$pathname."'");
	}
	
	public function rmdir($pathname) {
		$this->exec("rm -fr '".$pathname."'");
	}
}
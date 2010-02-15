<?php

/**
 * Amazon S3 Driver for the FS_Core class.
 * @note Requires the Amazon module
 *
 * @package		Modules
 * @subpackage	FileSystem
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class FS_Driver_S3 extends FS_Driver {
	private $config = array();
	private $cwd = null;
	private $s3;
	
	public function __construct($config=array()) {
		$this->config = $config;
		
		$this->s3 = new S3($this->config['access_key'], $this->config['secret_key']);
	}
	
	public function exec($command="") {
		// No concept on S3
	}
	
	public function file_exists($pathname="") {
		list($bucket, $path) = $this->bucket_path_info($pathname);
		if(empty($bucket)) return false;

		if(empty($path)) {
			return $this->s3->getBucket($bucket) == false ? false : true;
		} else {
			return $this->s3->getObject($bucket, $path) == false ? false : true;
		}
	}
	
	public function chdir($pathname) {
		// Need to implement
	}
	
	public function mkdir($pathname, $mode=0777, $recursive=false) {
		list($bucket, $path) = $this->bucket_path_info($pathname);
		if(empty($bucket)) return;
		
		if((empty($path) && !$this->file_exists($bucket)) || !$this->file_exists($bucket)) {
			$this->s3->putBucket($bucket);
		}
		
		if(!empty($path)) {
			// Need to implement
		}
	}
	
	public function chmod($pathname, $mode) {
		// Need to implement based on ACL
	}
	
	public function copy($source_pathname, $destination_pathname, $mime=NULL, $filename=NULL, $extras=array()) {
		list($bucket, $path) = $this->bucket_path_info($destination_pathname);
		
		if(!$this->s3->putObjectFile($source_pathname, $bucket, $path, S3::ACL_PUBLIC_READ, array(), $mime, str_replace("\n", " ", $filename), $extras)) {
			throw new Eight_Exception("Could not copy $source_pathname to bucket $bucket at path $path");
			return NO;
		}
		
		return YES;
	}
	
	public function unlink($pathname) {
		list($bucket, $path) = $this->bucket_path_info($pathname);
		if(empty($bucket)) return;
		
		if(!empty($path) && $this->file_exists($bucket)) {
			$this->s3->deleteObject($bucket, $path);
		}
	}
	
	public function rmdir($pathname) {
		list($bucket, $path) = $this->bucket_path_info($pathname);
		if(empty($bucket)) return;
		
		if((empty($path) && $this->file_exists($bucket))) {
			$this->s3->deleteBucket($bucket);
		} else if(!empty($path)) {
			// Need to implement
		}
	}
	
	private function bucket_path_info($path) {
		list($bucket, $path) = explode("/", $path, 2);
		return array($bucket.$this->config['bucket_suffix'], $path);
	}
}
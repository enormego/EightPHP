<?php
/**
 * Archive library bzip driver.
 *
 * @package		Modules
 * @subpackage	Archive
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Archive_Driver_Bzip_Core extends Archive_Driver {

	public function create($paths, $filename = NO) {
		$archive = new Archive('tar');

		foreach($paths as $set) {
			$archive->add($set[0], $set[1]);
		}

		$gzfile = bzcompress($archive->create());

		if($filename == NO) {
			return $gzfile;
		}

		if(substr($filename, -8) !== '.tar.bz2') {
			// Append tar extension
			$filename .= '.tar.bz2';
		}

		// Create the file in binary write mode
		$file = fopen($filename, 'wb');

		// Lock the file
		flock($file, LOCK_EX);

		// Write the tar file
		$return = fwrite($file, $gzfile);

		// Unlock the file
		flock($file, LOCK_UN);

		// Close the file
		fclose($file);

		return (bool) $return;
	}

	public function add_data($file, $name, $contents = nil) {
		return NO;
	}

} // End Archive_Bzip_Driver Class
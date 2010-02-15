<?php 
/**
 * Zip helper class.
 *
 * @version		$Id: zip.php 113 2009-02-19 23:18:53Z saverio $
 *
 * @package		System
 * @subpackage	Helpers
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class zip_Core {
	public static function contents($src_file) {
		$output = trim(shell_exec('zipinfo -1 "'.$src_file.'"'));
		return explode("\n", $output);
	}

	public static function is_valid($src_file) {
		$output = trim(shell_exec('unzip -T "'.$src_file.'"'));
		return str::e($output);
	}
	
	public static function delete_entry($src_file, $file, $recursive=YES) {
		if($recursive) {
			shell_exec('zip -dr "'.$src_file.'" "'.$file.'"');
		} else {
			shell_exec('zip -d "'.$src_file.'" "'.$file.'"');
		}
	}

	public static function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true) {
		if(!extension_loaded('zip')) {
			if(!dl('zip.so')) {
				exit('Zip Module could not be loaded.');
			}
		}

		$files = array();
		
		// Look for the resource and try something else...
		if(!is_resource(zip_open($src_file))) {
			$src_file = dirname($_SERVER['SCRIPT_FILENAME'])."/".$src_file;
		}
	 
		if(!is_resource($zip = zip_open($src_file))) {         
			return false; // No zip file found.
		}
		
		$splitter = ($create_zip_name_dir === true) ? "." : "/";
		
		if($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
		
		// Create the directories to the destination dir if they don't already exist
		self::create_dirs($dest_dir);
		
		// For every file in the zip-packet
		while($zip_entry = zip_read($zip)) {

			// If the file is not in the root dir
			$pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
				
			if($pos_last_slash !== false) {
				// Create the directory where the zip-entry should be saved (with a "/" at the end)
				self::create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
			}

			// Open the entry
			if(zip_entry_open($zip,$zip_entry,"r")) {
				// The name of the file to save on the disk
				$file_name = $dest_dir.zip_entry_name($zip_entry);

				// Check if the files should be overwritten or not
				if($overwrite === true || $overwrite === false && !is_file($file_name)) {
					// Get the content of the zip entry
					$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));          
					
					if(!is_dir($file_name)) {
						file_put_contents($file_name, $fstream);
						if(file_exists($file_name)) {
							chmod($file_name, 0777);
							$files[] = $file_name;
						} 
					}
		
					// Close the entry
					zip_entry_close($zip_entry);
				}     
			}
		}
			
		// Close the zip file
		zip_close($zip);
		
		return $files;
	}
	
	private static function create_dirs($path) {
		if(!is_dir($path)) {
			$directory_path = "";
			$directories = explode("/",$path);
			array_pop($directories);
			foreach($directories as $directory) {
				$directory_path .= $directory."/";
				if(!is_dir($directory_path)) {
					mkdir($directory_path);
					chmod($directory_path, 0777);
				}
			}
		}
	}
	
	/*
	 *  Method: create
	 *  Description: creates a zip file with the given file/directory or array of files.
	 *  Parameters:
	 * 		$save_path	string	path to save file at... ex: /blah/blah/test.zip
	 * 		$files		mixed	can be an array of files or a string containing a file or directory to be zipped.
	 */
	public static function create($save_path, $files) {
		// Since files can be an array or a path...
		if(!is_array($files)) {
			$files = array($files);
		}
		
		$archive = new Archive;
		
		// Loop through files...(or the one directory/file)
		foreach($files as $file) {
			if(file_exists($file)) {
				$archive->add($file);
			}
		}
		
		// Save the file
		$archive->save($save_path);
		
		// Status
		return file_exists($save_path);
	}
	
} // End Zip Archive Helper
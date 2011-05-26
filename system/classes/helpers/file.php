<?php
/**
 * File helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class file_Core {
	
	// Location of Mime Magic DB
	const MAGIC_DB = '/usr/share/file/magic';
	
	// Dir push/pop stack
	static $dir_stack = array();
	
	/**
	 * Moves into a new directory
	 * 
	 * @param	string	directory to move into
	 */
	public static function push_dir($dir) {
		array_push(self::$dir_stack, getcwd());
		chdir($dir);
	}
	
	/**
	 * Pops back to previous directory
	 */
	public static function pop_dir() {
		if(!arr::e(self::$dir_stack)) {
			$dir = array_pop(self::$dir_stack);
			chdir($dir);
		}
	}
	
	/**
	 * Recursive version of php's native glob() method
	 * 
	 * @param int		the pattern passed to glob()
	 * @param int		the flags passed to glob()
	 * @param string	the path to scan
	 * @return mixed	an array of files in the given path matching the pattern.
	 */

	public static function rglob($pattern='*', $flags = 0, $path='') {
	    $paths = glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
	    $files = glob($path.$pattern, $flags);
	    
		foreach ($paths as $path) {
			$files = array_merge($files, file::rglob($pattern, $flags, $path));
		}
		
	    return $files;
	}
	
	/**
	 * Attempt to get the mime type from a file. This method is horribly
	 * unreliable, due to PHP being horribly unreliable when it comes to
	 * determining the mime-type of a file.
	 *
	 * @param   string   filename
	 * @return  string|boolean   mime-type: if found, false: if not found
	 */
	public static function mime($filename) {
		// Make sure the file is readable
		if(!(is_file($filename) and is_readable($filename)))
			return NO;

		// Get the extension from the filename
		$extension = strtolower(substr(strrchr($filename, '.'), 1));

		if(preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension)) {
			// Disable error reporting
			$ER = error_reporting(0);

			// Use getimagesize() to find the mime type on images
			$mime = getimagesize($filename);

			// Turn error reporting back on
			error_reporting($ER);

			// Return the mime type
			if(isset($mime['mime']))
				return $mime['mime'];
		}

		try {
			if(function_exists('finfo_open') && ($finfo = finfo_open(FILEINFO_MIME, self::MAGIC_DB)) !== FALSE) {
				// Use the fileinfo extension
				$mime  = finfo_file($finfo, $filename);
				finfo_close($finfo);

				// Return the mime type
				return $mime;
			}
		} catch(Exception $e) { }

		if(ini_get('mime_magic.magicfile') and function_exists('mime_content_type')) {
			// Return the mime type using mime_content_type
			return mime_content_type($filename);
		}
		
		if(!empty($extension) and is_array($mime = Eight::config('mimes.'.$extension))) {
			// Return the mime-type guess, based on the extension
			return $mime[0];
		}

		// Unable to find the mime-type
		return NO;
	}

	/**
	 * Force a download of a file to the user's browser. This function is
	 * binary-safe and will work with any MIME type that Eight is aware of.
	 *
	 * @param   string  a file path or file name
	 * @param   mixed   data to be sent if the filename does not exist
	 * @param   string  suggested filename to display in the download
	 */
	public static function download($filename = nil, $data = nil, $nicename = nil) {
		if(empty($filename))
			return NO;

		if(is_file($filename)) {
			// Get the real path
			$filepath = str_replace('\\', '/', realpath($filename));

			// Set filesize
			$filesize = filesize($filepath);

			// Get filename
			$filename = substr(strrchr('/'.$filepath, '/'), 1);

			// Get extension
			$extension = strtolower(substr(strrchr($filepath, '.'), 1));
		} else {
			// Get filesize
			$filesize = strlen($data);

			// Make sure the filename does not have directory info
			$filename = substr(strrchr('/'.$filename, '/'), 1);

			// Get extension
			$extension = strtolower(substr(strrchr($filename, '.'), 1));
		}

		// Get the mime type of the file
		$mime = Eight::config('mimes.'.$extension);

		if(empty($mime)) {
			// Set a default mime if none was found
			$mime = array('application/octet-stream');
		}

		// Generate the server headers
		header('Content-Type: '.$mime[0]);
		header('Content-Disposition: attachment; filename="'.(empty($nicename) ? $filename : $nicename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.sprintf('%d', $filesize));

		// More caching prevention
		header('Expires: 0');

		if(Eight::user_agent('browser') === 'Internet Explorer') {
			// Send IE headers
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			// Send normal headers
			header('Pragma: no-cache');
		}

		// Clear the output buffer
		Eight::close_buffers(NO);

		if(isset($filepath)) {
			// Open the file
			$handle = fopen($filepath, 'rb');

			// Send the file data
			fpassthru($handle);

			// Close the file
			fclose($handle);
		} else {
			// Send the file data
			echo $data;
		}
	}

	/**
	 * Split a file into pieces matching a specific size.
	 *
	 * @param   string   file to be split
	 * @param   string   directory to output to, defaults to the same directory as the file
	 * @param   integer  size, in MB, for each chunk to be
	 * @return  integer  The number of pieces that were created.
	 */
	public static function split($filename, $output_dir = NO, $piece_size = 10) {
		// Find output dir
		$output_dir = ($output_dir == NO) ? pathinfo(str_replace('\\', '/', realpath($filename)), PATHINFO_DIRNAME) : str_replace('\\', '/', realpath($output_dir));
		$output_dir = rtrim($output_dir, '/').'/';

		// Open files for writing
		$input_file = fopen($filename, 'rb');

		// Change the piece size to bytes
		$piece_size = 1024 * 1024 * (int) $piece_size; // Size in bytes

		// Set up reading variables
		$read  = 0; // Number of bytes read
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Split the file
		while(!feof($input_file)) {
			// Open a new piece
			$piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT);
			$piece_open = @fopen($piece_name, 'wb+') or die('Could not write piece '.$piece_name);

			// Fill the current piece
			while($read < $piece_size and $data = fread($input_file, $chunk)) {
				fwrite($piece_open, $data) or die('Could not write to open piece '.$piece_name);
				$read += $chunk;
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare to open a new piece
			$read = 0;
			$piece++;

			// Make sure that piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded, try a larger piece size');
		}

		// Close input file
		fclose($input_file);

		// Returns the number of pieces that were created
		return ($piece - 1);
	}

	/**
	 * Join a split file into a whole file.
	 *
	 * @param   string   split filename, without .000 extension
	 * @param   string   output filename, if different then an the filename
	 * @return  integer  The number of pieces that were joined.
	 */
	public static function join($filename, $output = NO) {
		if($output == NO)
			$output = $filename;

		// Set up reading variables
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Open output file
		$output_file = @fopen($output, 'wb+') or die('Could not open output file '.$output);

		// Read each piece
		while($piece_open = @fopen(($piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT)), 'rb')) {
			// Write the piece into the output file
			while(!feof($piece_open)) {
				fwrite($output_file, fread($piece_open, $chunk));
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare for a new piece
			$piece++;

			// Make sure piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded');
		}

		// Close the output file
		fclose($output_file);

		// Return the number of pieces joined
		return ($piece - 1);
	}
	
	/**
	 * Loops through the mimes config array and finds the extension for a given mime type.
	 * Might be able to speed this one up a bit...not sure.
	 *
	 * @param	string		mime type 
	 * @return	string		extension for given mime type
	 */
	public static function mime_to_ext($mime) {
		$mimes = Eight::config('mimes');
		foreach($mimes as $k=>$m) {
			foreach($m as $v) {
				if($mime == $v) {
					return $k;
				}
			}
		}
	}
	
	public static function ext($file) {
		if(substr_count($file, ".") > 0) {
			return substr(strrchr($file, "."), 1);
		} else {
			return NULL;
		}
	}
	
	public static function without_ext($file) {
		if(substr_count($file, ".") > 0) {
			return substr($file, 0, strrpos($file, "."));
		} else {
			return $file;
		}
	}
	
	public static function ext_replace($file, $new) {
		$old = self::ext($file);
		return substr_replace($file, $new, strlen($old)*-1);
	}
	
	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to true, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @access	public
	 * @param	string	path to file
	 * @param	bool	whether to delete any directories found in the path
	 * @return	bool
	 */	
	public static function delete_files($path, $del_dir = false, $level = 0) {	
		// Trim the trailing slash
		$path = preg_replace("|^(.+?)/*$|", "\\1", $path);
				
		if ( ! $current_dir = @opendir($path))
			return;
		
		while(false !== ($filename = @readdir($current_dir))) {
			if ($filename != "." and $filename != "..") {
				if (is_dir($path.'/'.$filename)) {
					$level++;
					self::delete_files($path.'/'.$filename, $del_dir, $level);
				} else {
					unlink($path.'/'.$filename);
				}
			}
		}
		@closedir($current_dir);
		
		if ($del_dir == true and $level > 0) {
			@rmdir($path);
		}
	}
	
} // End file
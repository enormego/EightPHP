<?php
/**
 * Archive library.
 *
 * @package		Modules
 * @subpackage	Archive
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Archive_Core {

	// Files and directories
	protected $paths;

	// Driver instance
	protected $driver;

	/**
	 * Loads the archive driver.
	 *
	 * @throws  Eight_Exception
	 * @param   string   type of archive to create
	 * @return  void
	 */
	public function __construct($type = nil) {
		$type = empty($type) ? 'zip' : $type;

		// Set driver name
		$driver = 'Archive_Driver_'.ucfirst($type);

		// Load the driver
		if(!Eight::auto_load($driver))
			throw new Eight_Exception('core.driver_not_found', $type, get_class($this));

		// Initialize the driver
		$this->driver = new $driver();

		// Validate the driver
		if(!($this->driver instanceof Archive_Driver))
			throw new Eight_Exception('core.driver_implements', $type, get_class($this), 'Archive_Driver');

		Eight::log('debug', 'Archive Library initialized');
	}

	/**
	 * Adds files or directories, recursively, to an archive.
	 *
	 * @param   string   file or directory to add
	 * @param   string   name to use for the given file or directory
	 * @param   bool     add files recursively, used with directories
	 * @return  object
	 */
	public function add($path, $name = nil, $recursive = nil) {
		// Normalize to forward slashes
		$path = str_replace('\\', '/', $path);

		// Set the name
		empty($name) and $name = $path;

		if(is_dir($path)) {
			// Force directories to end with a slash
			$path = rtrim($path, '/').'/';
			$name = rtrim($name, '/').'/';

			// Add the directory to the paths
			$this->paths[] = array($path, $name);

			if($recursive === YES) {
				$dir = opendir($path);
				while(($file = readdir($dir)) !== NO) {
					// Do not add hidden files or directories
					if(substr($file, 0, 1) === '.')
						continue;

					// Add directory contents
					$this->add($path.$file, $name.$file, YES);
				}
				closedir($dir);
			}
		} else {
			$this->paths[] = array($path, $name);
		}

		return $this;
	}

	/**
	 * Creates an archive and saves it into a file.
	 *
	 * @throws  Eight_Exception
	 * @param   string   archive filename
	 * @return  boolean
	 */
	public function save($filename) {
		// Get the directory name
		$directory = pathinfo($filename, PATHINFO_DIRNAME);

		if(!is_writable($directory))
			throw new Eight_Exception('archive.directory_unwritable', $directory);

		if(is_file($filename)) {
			// Unable to write to the file
			if(!is_writable($filename))
				throw new Eight_Exception('archive.filename_conflict', $filename);

			// Remove the file
			unlink($filename);
		}

		return $this->driver->create($this->paths, $filename);
	}

	/**
	 * Creates a raw archive file and returns it.
	 *
	 * @return  string
	 */
	public function create() {
		return $this->driver->create($this->paths);
	}

	/**
	 * Forces a download of a created archive.
	 *
	 * @param   string   name of the file that will be downloaded
	 * @return  void
	 */
	public function download($filename) {
		file::download($filename, $this->driver->create($this->paths));
	}

} // End Archive
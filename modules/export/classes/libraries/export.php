<?php

/**
 * Export class designed to provide a nice interface for all the drivers.
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 * 
 */

class Export_Core {
	private $config;
	private $driver;
	
	public function __construct($config=array()) {
		// Set the config
		$this->config = $config;
		
		// Set a filename, if we don't have one
		if(str::e($this->config['filename'])) {
			$this->config['filename'] = date("Y-m-d_g-ia");
		}

		// Build driver class
		$driver = "Export_Driver_".trim((strtoupper($config['driver'])));
		
		// Load the driver
		if (!Eight::auto_load($driver))
			throw new Export_Exception('export.driver_not_supported', $config['driver']);

		// Initialize the driver
		$this->driver = new $driver($this->config);

		// Validate the driver
		if (!($this->driver instanceof Export_Driver))
			throw new Export_Exception('export.driver_not_supported', 'Export drivers must use the Export_Driver interface.');
		
		// Set the columns
		if(!arr::e($this->config['columns'])) {
			$this->driver->set_columns($this->config['columns']);
		}
	}
	
	/**
	 * Add a row to the current data set
	 * If there is more than one argument, each argument is considered a column
	 *
	 * @param	mixed $data array or the first argument
	 * @return	Export instance
	 */
	public function add_row($data) {
		if(func_num_args() > 1) {
			$row = func_get_args();
		} else {
			$row = $data;
		}
		
		$this->driver->add_row($row);

		return $this;
	}
	
	/**
	 * Add multiple rows to the current data set
	 *
	 * @param  	array $rows	an array of rows to be added
	 * @return	Export instance
	 */
	public function add_rows($rows) {
		foreach($rows as $row) {
			$this->add_row($row);
		}
		
		return $this;
	}
	
	/**
	 * Sends a compiled file to the browser for this export data type
	 */
	public function to_browser() {
		header("Content-type: ".$this->driver->content_type());
		header("Content-disposition: attachment; filename=".$this->config['filename'].".".$this->driver->ext());
		$this->driver->to_browser();
	}
	
	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function to_string() {
		return $this->driver->to_string();
	}
	
	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->to_string();
	}
}

?>
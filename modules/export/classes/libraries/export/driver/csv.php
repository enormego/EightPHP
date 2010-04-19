<?php
/**
 *  Export Driver CSV
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Export_Driver_CSV_Core extends Export_Driver {
	private $tmp_fname;
	private $tmp_fhandle;
	private $delimiter = ",";
	private $enclosure = "\"";
	
	public function __construct($config=array()) {
		if(!str::e($config['delimiter'])) {
			$this->delimiter = $config['delimiter'];
		}
		
		if(!str::e($config['enclosure'])) {
			$this->enclosure = $config['enclosure'];
		}
		
		$this->tmp_fname = tempnam(sys_get_temp_dir(), "export");
		$this->tmp_fhandle = fopen($this->tmp_fname, 'rw+');
	}
	
	/**
	 * Sets the columns, optional
	 */
	public function set_columns($columns) {
		rewind($this->tmp_fhandle);
		fputcsv($this->tmp_fhandle, $columns, $this->delimiter, $this->enclosure);
		fseek($this->tmp_fhandle, 0, SEEK_END);
	}

	/**
	 * File extension for this export data type
	 *
	 * @return string
	 */
	public function ext() {
		return "csv";
	}

	/**
	 * MIME Content Type for this export data type
	 *
	 * @return string
	 */
	public function content_type() {
		return "application/csv";
	}

	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function to_string() {
		rewind($this->tmp_fhandle);

		$contents = '';
		while (!feof($this->tmp_fhandle)) {
			$contents .= fread($this->tmp_fhandle, 8192);
		}

		fseek($this->tmp_fhandle, 0, SEEK_END);

		return $contents;
	}

	/**
	 * Sends a compiled file to the browser for this export data type
	 */
	public function to_browser() {
		rewind($this->tmp_fhandle);
		fpassthru($this->tmp_fhandle);
		fseek($this->tmp_fhandle, 0, SEEK_END);
	}

	/**
	 * Add a row to the current data set
	 *
	 * @param  array $row
	 */
	public function add_row($row) {
		fputcsv($this->tmp_fhandle, $row, $this->delimiter, $this->enclosure);
	}
	
	public function __destruct() {
		@fclose($this->tmp_fhandle);
		@unlink($this->tmp_fname);
	}
}

<?php
/**
 *  Export Driver JSON
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Export_Driver_JSON_Core extends Export_Driver_XML {
	private $container = "entries";
	
	public function __construct($config=array()) {
		if(!str::e($config['container'])) {
			$this->container = $config['container'];
		}
	}

	/**
	 * File extension for this export data type
	 *
	 * @return string
	 */
	public function ext() {
		return "js";
	}

	/**
	 * MIME Content Type for this export data type
	 *
	 * @return string
	 */
	public function content_type() {
		return "application/json";
	}

	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function to_string() {
		return json_encode(array($this->container => $this->rows));
	}
}

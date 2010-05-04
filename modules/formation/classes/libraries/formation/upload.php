<?php
/**
 * Formation upload input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Upload_Core extends Formation_Input {

	protected $data = array(
		'class' => 'upload',
		'value' => '',
	);

	protected $protect = array('type', 'label', 'value');

	// Upload data
	protected $upload;

	// Upload directory and filename
	protected $directory;
	protected $filename;

	public function __construct($name, $filename = NO, $formation=nil) {
		if(is_object($filename))
			parent::__construct($name, $filename);
		else
			parent::__construct($name, $formation);

		if ( ! empty($_FILES[$name])) {
			if (empty($_FILES[$name]['tmp_name']) OR is_uploaded_file($_FILES[$name]['tmp_name'])) {
				// Cache the upload data in this object
				$this->upload = $_FILES[$name];

				// Hack to allow file-only inputs, where no POST data is present
				$_POST[$name] = $this->upload['name'];

				// Set the filename
				$this->filename = empty($filename) ? NO : $filename;
			} else {
				// Attempt to delete the invalid file
				is_writable($_FILES[$name]['tmp_name']) and unlink($_FILES[$name]['tmp_name']);

				// Invalid file upload, possible hacking attempt
				unset($_FILES[$name]);
			}
		}
	}

	/**
	 * Sets the upload directory.
	 *
	 * @param   string   upload directory
	 * @return  void
	 */
	public function directory($dir = nil) {
		// Use the global upload directory by default
		empty($dir) and $dir = Eight::config('upload.directory');

		// Make the path asbolute and normalize it
		$directory = str_replace('\\', '/', realpath($dir)).'/';

		// Make sure the upload director is valid and writable
		if ($directory === '/' OR ! is_dir($directory) OR ! is_writable($directory))
			throw new Eight_Exception('upload.not_writable', $dir);

		$this->directory = $directory;
		
		return $this;
	}

	public function validate() {
		// The upload directory must always be set
		empty($this->directory) and $this->directory();

		// By default, there is no uploaded file
		$filename = '';

		if ($status = parent::validate() AND $this->upload['error'] === UPLOAD_ERR_OK) {
			// Set the filename to the original name
			$filename = $this->upload['name'];

			if (Eight::config('upload.remove_spaces')) {
				// Remove spaces, due to global upload configuration
				$filename = preg_replace('/\s+/', '_', $this->data['value']);
			}

			if (file_exists($filepath = $this->directory.$filename)) {
				if ($this->filename !== YES OR ! is_writable($filepath)) {
					// Prefix the file so that the filename is unique
					$filepath = $this->directory.'uploadfile-'.uniqid(time()).'-'.$this->upload['name'];
				}
			}

			// Move the uploaded file to the upload directory
			move_uploaded_file($this->upload['tmp_name'], $filepath);
		}

		if ( ! empty($_POST[$this->data['name']])) {
			// Reset the POST value to the new filename
			$this->data['value'] = $_POST[$this->data['name']] = empty($filepath) ? '' : $filepath;
		}

		return $status;
	}

	protected function rule_required() {
		if (empty($this->upload) OR $this->upload['error'] === UPLOAD_ERR_NO_FILE) {
			$this->errors['required'] = YES;
		}
	}

	public function rule_allow() {
		if (empty($this->upload['tmp_name']) OR count($types = func_get_args()) == 0)
			return;

        if (($mime = file::mime($this->upload['tmp_name'])) === NO) {
			// Trust the browser
			$mime = $this->upload['type'];
		}

		// Allow nothing by default
		$allow = NO;

		foreach ($types as $type) {
			// Load the mime types 
			$type = Eight::config('mimes.'.$type); 
			
			if (is_array($type) AND in_array($mime, $type)) {
				// Type is valid
				$allow = YES;
				break;
			}
		}

		if ($allow === NO) {
			$this->errors['invalid_type'] = YES;
		}
	}

	public function rule_size($size) {
		// Skip the field if it is empty
		if (empty($this->upload) OR $this->upload['error'] === UPLOAD_ERR_NO_FILE)
			return;

		$bytes = (int) $size;

		switch (substr($size, -2)) {
			case 'GB': $bytes *= 1024;
			case 'MB': $bytes *= 1024;
			case 'KB': $bytes *= 1024;
			default: break;
		}

		if (empty($this->upload['size']) OR $this->upload['size'] > $bytes) {
			$this->errors['max_size'] = array($size);
		}
	}
	
	public function rule_randomize() {
		if (empty($this->upload) or $this->upload['error'] === UPLOAD_ERR_NO_FILE)
			return;

		$this->upload['name'] = md5(time()).'.'.end(explode('.', $this->upload['name']));
	}

	protected function html_element() {
		// Create a temp copy
		$data = $this->data;
		
		// Remove Message
		unset($data['message']);
		
		return form::upload($data);
	}

} // End Formation Upload
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation checkbox input library.
 *
 * @version		$Id: checkbox.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Checkbox_Core extends Form_Input {

	protected $data = array(
		'type' => 'checkbox',
		'class' => 'checkbox',
		'value' => '1',
		'checked' => NO,
	);

	protected $protect = array('type');

	public function __get($key) {
		if ($key == 'value') {
			// Return the value if the checkbox is checked
			return $this->data['checked'] ? $this->data['value'] : nil;
		}

		return parent::__get($key);
	}

	public function label($val = nil) {
		if ($val === nil) {
			// Do not display labels for checkboxes, labels wrap checkboxes
			return '';
		} else {
			$this->data['label'] = ($val === YES) ? utf8::ucwords(inflector::humanize($this->name)) : $val;
			return $this;
		}
	}

	protected function html_element() {
		// Import the data
		$data = $this->data;
		
		if (empty($data['checked'])) {
			// Not checked
			unset($data['checked']);
		} else {
			// Is checked
			$data['checked'] = 'checked';
		} 


		if ($label = arr::remove('label', $data)) {
			// There must be one space before the text
			$label = ' '.ltrim($label);
		}

		return '<label>'.form::input($data).'<span>'.$label.'</span></label>';
	}

	protected function load_value() {
		if (is_bool($this->valid))
			return;

		// Makes the box checked if the value from POST is the same as the current value
		$this->data['checked'] = ($this->input_value($this->name) == $this->data['value']);
	}

} // End Form Checkbox
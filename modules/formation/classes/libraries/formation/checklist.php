<?php
/**
 * Formation checklist input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Checklist_Core extends Formation_Input {

	protected $data = array(
		'name'    => '',
		'type'    => 'checkbox',
		'class'   => 'checklist',
		'options' => array(),
	);

	protected $protect = array('name', 'type');

	public function __construct($name, $formation) {
		$this->data['name'] = $name;
		$this->formation = $formation;
	}

	public function __get($key) {
		if ($key == 'value') {
			// Return the currently checked values
			$array = array();
			foreach($this->data['options'] as $id => $opt) {
				// Return the options that are checked
				($opt[1] === YES) and $array[] = $id;
			}
			return $array;
		}

		return parent::__get($key);
	}

	public function render() {
		// Import base data
		$base_data = $this->data;

		// Make it an array
		$base_data['name'] .= '[]';

		// Newline
		$nl = "\n";
		
		// Element
		$element = $base_data['type'];

		$checklist = '<ul class="'.arr::remove('class', $base_data).'">'.$nl;
		foreach(arr::remove('options', $base_data) as $val => $opt) {
			// New set of input data
			$data = $base_data;

			// Get the title and checked status
			list ($title, $checked) = $opt;

			// Set the name, value, and checked status
			$data['value']   = $val;
			$data['checked'] = $checked;

			$checklist .= '<li><label>'.form::$element($data).' '.$title.'</label></li>'.$nl;
		}
		$checklist .= '</ul>';

		return $checklist;
	}

	protected function load_value() {
		foreach($this->data['options'] as $val => $checked) {
			if ($input = $this->input_value($this->data['name'])) {
				$this->data['options'][$val][1] = in_array($val, $input);
			} else {
				$this->data['options'][$val][1] = NO;
			}
		}
	}

} // End Formation Checklist
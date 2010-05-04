<?php
/**
 * Formation group library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Group_Core extends Formation {

	protected $data = array(
		'type'  => 'group',
		'name'	=> '',
		'class' => 'group',
		'label' => '',
		'message' => ''
	);
	
	// Default layout structure
	public $layout = 'rows';
	
	// Input method
	public $method;

	public function __construct($name = nil, $class = 'group', $formation=nil) {
		$this->data['name'] = $name;
		$this->data['class'] = $class;

		// Set dummy data so we don't get errors
		$this->attr['action'] = '';
		$this->attr['method'] = 'post';
		$this->formation = $formation;
	}
	
	/**
	 * Accessor to the form this is attached to
	 */
	public function form() {
		return $this->formation;
	}

	public function __get($key) {
		if ($key == 'type' || $key == 'name') {
			return $this->data[$key];
		} elseif($key == 'html_class') {
			return $this->key;
		} else {
			return parent::__get($key);
		}
	}

	public function __set($key, $val) {
		if ($key == 'method') {
			$this->attr['method'] = $val;
		} else {
			$this->$key = $val;
		}
	}
	
	public function layout($layout) {
		$this->layout = $layout;
		return $this;
	}
	
	public function style($style) {
		$this->style = $style;
		return $this;
	}
	
	public function label($val = nil) {
		if ($val === nil) {
			if ($label = $this->data['label']) {
				return $this->data['label'];
			}
		} else {
			$this->data['label'] = ($val === YES) ? ucwords(inflector::humanize($this->data['name'])) : $val;
			return $this;
		}
	}

	public function message($val = nil) {
		if ($val === nil) {
			return $this->data['message'];
		} else {
			$this->data['message'] = $val;
			return $this;
		}
	}

	public function render() {
		// No Sir, we don't want any html today thank you
		return;
	}

} // End Formation Group
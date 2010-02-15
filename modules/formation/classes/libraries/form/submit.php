<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation submit input library.
 *
 * @version		$Id: submit.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Submit_Core extends Form_Input {

	protected $data = array(
		'type'  => 'submit',
		'class' => 'submit'
	);

	protected $protect = array('type');

	public function __construct($value, $formation) {
		$this->data['value'] = $value;
		$this->formation = $formation;
	}

	public function render() {
		$data = $this->data;
		unset($data['label']);

		return form::button($data);
	}

	public function validate() {
		// Submit buttons do not need to be validated
		return $this->is_valid = YES;
	}

} // End Form Submit
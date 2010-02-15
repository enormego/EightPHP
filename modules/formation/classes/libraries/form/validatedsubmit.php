<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation submit input library.
 *
 * @version		$Id: submit.php 13 2008-09-18 20:14:08Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Validatedsubmit_Core extends Form_Input {

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

} // End Form Submit
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation hidden input library.
 *
 * @version		$Id: hidden.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Hidden_Core extends Form_Input {

	protected $data = array(
		'name'  => '',
		'value' => '',
	);

	public function render() {
		return form::hidden($this->data);
	}

} // End Form Hidden
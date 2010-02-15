<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation radio input library.
 *
 * @version		$Id: radio.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Radio_Core extends Form_Checkbox {

	protected $data = array(
		'type' => 'radio',
		'class' => 'radio',
		'value' => '1',
		'checked' => NO,
	);

} // End Form_Radio
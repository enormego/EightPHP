<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation password input library.
 *
 * @version		$Id: password.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Password_Core extends Form_Input {

	protected $data = array(
		'type'  => 'password',
		'class' => 'password',
		'value' => '',
	);

	protected $protect = array('type');

} // End Form Password
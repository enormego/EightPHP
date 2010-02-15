<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation Captcha input library.
 *
 * @version		$Id$
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Captcha_Core extends Form_Input {

	protected $data = array(
		'type'  => 'text',
		'class' => 'textbox',
		'value' => '',
		'group' => 'default'
	);
	
	protected $protect = array('type');

	public function __construct() {
		$args = func_get_args();
		call_user_func_array(array("parent", "__construct"), $args);
		$this->rules('required|captcha');
	}
	
	public function render() {
		$config = Eight::config('captcha.'.$this->group);
		return parent::render() . "<br /><img src=\"".url::site('captcha/'.$this->group)."\" width=\"".$config['width']."\" height=\"".$config['height']."\" />";
	}

	protected function rule_captcha() {
	    if ($this->value === '' OR $this->value === NULL) {
	        $this->errors['required'] = TRUE;
	    } elseif (Captcha::valid($this->value, $this->group) == FALSE) {
	        $this->errors['captcha'] = TRUE;
	    }
	}

} // End Form Captcha
<?php
/**
 * Outputs the dynamic Captcha resource.
 * Usage: Call the Captcha controller from a view, e.g.
 *        <img src="<?php echo url::site('captcha') ?>" />
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Controller_Captcha extends Controller {

	public function __call($method, $args) {
		// Output the Captcha challenge resource (no html)
		// Pull the config group name from the URL
		Captcha::factory($method)->render(NO);
	}
	
	public function _render($data=array()) {
		// Do Nothing
	}

} // End Captcha_Controller
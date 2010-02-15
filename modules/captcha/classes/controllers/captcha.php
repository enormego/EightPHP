<?php
/**
 * Outputs the dynamic Captcha resource.
 * Usage: Call the Captcha controller from a view, e.g.
 *        <img src="<?php echo url::site('captcha') ?>" />
 *
 * @version		$Id: captcha.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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
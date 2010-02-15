<?php
/**
 * Captcha driver for "riddle" style.
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Captcha_Driver_Riddle_Core extends Captcha_Driver {

	private $riddle;

	/**
	 * Generates a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	public function generate_challenge() {
		// Load riddles from the current language
		$riddles = Eight::lang('captcha.riddles');

		// Pick a random riddle
		$riddle = $riddles[array_rand($riddles)];

		// Store the question for output
		$this->riddle = $riddle[0];

		// Return the answer
		return $riddle[1];
	}

	/**
	 * Outputs the Captcha riddle.
	 *
	 * @param   boolean  html output
	 * @return  mixed
	 */
	public function render($html) {
		return $this->riddle;
	}

} // End Captcha Riddle Driver Class
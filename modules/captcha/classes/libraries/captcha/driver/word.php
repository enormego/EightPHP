<?php
/**
 * Captcha driver for "word" style.
 *
 * @version		$Id: word.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Captcha_Driver_Word_Core extends Captcha_Basic_Driver {

	/**
	 * Generates a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	public function generate_challenge() {
		// Load words from the current language and randomize them
		$words = Eight::lang('captcha.words');
		shuffle($words);

		// Loop over each word...
		foreach($words as $word) {
			// ...until we find one of the desired length
			if(abs(Captcha::$config['complexity'] - strlen($word)) < 2)
				return strtoupper($word);
		}

		// Return any random word as final fallback
		return strtoupper($words[array_rand($words)]);
	}

} // End Captcha Word Driver Class
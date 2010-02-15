<?php
/**
 * Captcha driver for "math" style.
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Captcha_Driver_Math_Core extends Captcha_Driver {

	private $math_exercice;

	/**
	 * Generates a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	public function generate_challenge() {
		// Easy
		if(Captcha::$config['complexity'] < 4) {
			$numbers[] = mt_rand(1, 5);
			$numbers[] = mt_rand(1, 4);
		}
		// Normal
		elseif(Captcha::$config['complexity'] < 7) {
			$numbers[] = mt_rand(10, 20);
			$numbers[] = mt_rand(1, 10);
		}
		// Difficult, well, not really ;)
		else {
			$numbers[] = mt_rand(100, 200);
			$numbers[] = mt_rand(10, 20);
			$numbers[] = mt_rand(1, 10);
		}

		// Store the question for output
		$this->math_exercice = implode(' + ', $numbers).' = ';

		// Return the answer
		return array_sum($numbers);
	}

	/**
	 * Outputs the Captcha riddle.
	 *
	 * @param   boolean  html output
	 * @return  mixed
	 */
	public function render($html) {
		return $this->math_exercice;
	}

} // End Captcha Math Driver Class
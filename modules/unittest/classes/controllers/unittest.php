<?php
/**
 * UnitTest controller.
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Controller_Unittest extends Controller_Core {

	const ALLOW_PRODUCTION = NO;

	public function index() {
		// Run tests and show results!
		echo new UnitTest;
	}

}
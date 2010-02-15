<?php
/**
 * Unit_Test controller.
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Controller_Unit_test extends Controller {

	const ALLOW_PRODUCTION = NO;

	public function index() {
		// Run tests and show results!
		echo new Unit_Test;
	}

}
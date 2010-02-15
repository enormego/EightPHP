<?php
/**
 * Unit_Test controller.
 *
 * @version		$Id: unit_test.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Controller_Unit_test extends Controller {

	const ALLOW_PRODUCTION = NO;

	public function index() {
		// Run tests and show results!
		echo new Unit_Test;
	}

}
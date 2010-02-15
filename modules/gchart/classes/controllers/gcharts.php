<?php

/**
 * Example Controller for the GCharts Module
 *
 * @package		Modules
 * @subpackage	GoogleCharts
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
 
class Controller_Gcharts extends Controller {
	public function index() {
		$this->html .= View::factory('gcharts/charts');
	}
}
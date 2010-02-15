<?php

/**
 * GChart Grouped Bar Chart subclass
 *
 * @package		Modules
 * @subpackage	GoogleCharts
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 *
 * @link		http://code.google.com/p/gchartphp/
 * @note		This was originally branched from an open source project.
 *				The code has been rewritten and refactored to work with
 *				Eight, and to work in general.
 */

class GChart_Bar_Grouped extends GChart_Bar {
	
	public function __construct() {
		$this->type = 5;
	}
	
	public function setHorizontal($isHorizontal) {
		if($isHorizontal) {
			$this->type = 4;
		} else{
			$this->type = 5;
		}
		$this->isHoriz = $isHorizontal;
	}

}

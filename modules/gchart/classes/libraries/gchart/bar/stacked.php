<?php

/**
 * GChart Stacked Bar Chart subclass
 *
 * @package		Modules
 * @subpackage	GoogleCharts
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 *
 * @link		http://code.google.com/p/gchartphp/
 * @note		This was originally branched from an open source project.
 *				The code has been rewritten and refactored to work with
 *				Eight, and to work in general.
 */

class GChart_Bar_Stacked extends GChart_Bar {
	
	public function __construct() {
		$this->type = 3;
	}

	public function setBarCount() {
		$this->totalBars = gchartutil::getMaxCountOfArray($this->values);
	}
	
	public function setHorizontal($isHorizontal) {
		if($isHorizontal) {
			$this->type = 2;
		} else {
			$this->type = 3;
		}
		$this->isHoriz = $isHorizontal;
	}

	protected function scaleValues() {
		$this->setScalar();
		$this->scaledValues = gchartutil::getScaledArray($this->values, $this->scalar);
	}
	
	function setScalar() {
		$maxValue = 100;
		$maxValue = max($maxValue, gchartutil::getMaxOfArray(gchartutil::addArrays($this->values)));
		if($maxValue < 100)
			$this->scalar = 1;
		else
			$this->scalar = 100 / $maxValue;
	}
	
}

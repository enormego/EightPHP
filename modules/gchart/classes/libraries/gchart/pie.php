<?php

/**
 * GChart Pie Chart subclass
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

class GChart_Pie extends GChart {
	public $no_labels = false;
	
	public function __construct() {
		$this->type = 6;
		$this->width = $this->height * 1.5;
	}
	
	public function setScalar() {
		return 1;
	}

	protected function getAxesString() {
		return "";
	}
	
	public function getUrl() {
		$retStr = parent::getUrl();
		if(!$this->no_labels) $retStr .= "&chl=".$this->encodeData($this->valueLabels,"", "|");
		return $retStr;
	}
	
	private function getScaledArray($unscaledArray, $scalar) {
		return $unscaledArray;		
	}
	
	public function set3D($is3d) {
		if($is3d) {
			$this->type = 7;
			$this->width = $this->height * 2;
		} else{
			$this->type = 6;
			$this->width = $this->height * 1.5;
		}
	}
}

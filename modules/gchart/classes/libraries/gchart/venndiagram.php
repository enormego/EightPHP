<?php

/**
 * GChart VennDiagram Chart subclass
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

class GChart_VennDiagram extends GChart {
	private $intersections = array(0,0,0,0);
	
	public function __construct() {
		$this->type = 8;
	}
	
	public function addIntersections($mixed) {
		$this->intersections = $mixed;
	}
	
	protected function getAxesString() {
		return "";
	}
	
	public function getUrl() {
		$retStr = parent::getUrl();
//		$retStr .= "&chl=".$this->encodeData($this->valueLabels,"", "|");
		return $retStr;
	}
	
	protected function getDataSetString() {
		$fullDataSet = array_splice($this->scaledValues[0], 0, 3);
		while(count($fullDataSet)<3) {
			array_push($fullDataSet, 0);
		}
		
		$scaledIntersections = gchartutil::getScaledArray($this->intersections, $this->scalar);
		foreach($scaledIntersections as $temp) {
			array_push($fullDataSet, $temp);
		}
		$fullDataSet = array_splice($fullDataSet, 0, 7);
		while(count($fullDataSet)<7) {
			array_push($fullDataSet, 0);
		}
		
		return "&chd=".$this->dataEncodingType.":".$this->encodeData($fullDataSet,"" ,",");
	}
	
}

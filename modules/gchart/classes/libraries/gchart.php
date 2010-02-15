<?php

/**
 * GChart core class, responsible for generating all charts.
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

class GChart_Core {
	private $baseUrl = "http://chart.apis.google.com/chart?";
	protected $scalar = 1;
	
	public $types = array ("lc","lxy","bhs","bvs","bhg","bvg","p","p3","v","s","ls");
	public $type = 1;
	public $dataEncodingType = "t";
	public $values = Array();
	protected $scaledValues = Array();
	public $valueLabels;
	public $xAxisLabels;
	public $axesString = "&chxt=x,y&chxr=0,1,4|1,1,10";
	public $dataColors;
	public $width = 200; //default
	public $height = 200; //default
	private $title;
	public $legend_position = '&chdlp=r';
	public $backgrounds;
	
	public function setTitle($newTitle) {
		$this->title = str_replace("\r\n", "|", $newTitle);
		$this->title = str_replace(" ", "+", $this->title);
	}
	
	public function addBackground($gBackground) {
		if(!isset($this->backgrounds)) {
			$this->backgrounds = array($gBackground);
			return;
		}
		array_push($this->backgrounds, $gBackground);
	}
	
	protected function encodeData($data, $encoding, $separator) {
		switch ($this->dataEncodingType) {
			case "s":
				return $this->simpleEncodeData();
			case "e":
				return $this->extendedEncodeData();
			default:{
				$retStr = $this->textEncodeData($data, $separator, "|");
				$retStr = trim($retStr, "|");
				return $retStr;					
				}
		}
	}
	
	private function textEncodeData($data, $separator, $datasetSeparator) {
		$retStr = "";
		if(!is_array($data))
			return $data;
		foreach($data as $currValue) {
			if(is_array($currValue))
				$retStr .= $this->textEncodeData($currValue, $separator, $datasetSeparator);
			else
				$retStr .= $currValue.$separator;
		}
			
		$retStr = trim($retStr, $separator);
		$retStr .= $datasetSeparator;
		return $retStr;
	}
	
	public function addDataSet($dataArray) {
		array_push($this->values, $dataArray);
	}
	
	public function clearDataSets() {
		$this->values = Array();
	}
	
	private function simpleEncodeData() {
		return "";
	}
	
	private function extendedEncodeData() {
		return "";
	}
	
	protected function prepForUrl() {
		$this->scaleValues();
	}
	
	protected function getDataSetString() {
		return "&chd=".$this->dataEncodingType.":".$this->encodeData($this->scaledValues,"" ,",");
	}
	
	protected function getAxesString() {
		return $this->axesString;
	}
	
	public function positionLegend($where) {
		if($where == 'none') {
			$this->legend_position = "";
		} else {
			/*
			 * b = Bottom
			 * t = Top
			 * l = Left
			 * r = Right
			 * 
			 */
			
			switch($where) {
				case 'bottom':
					$where = 'b';
					break;
				case 'top':
					$where = 't';
					break;
				case 'left':
					$where = 'l';
					break;
				default:
				case 'right':
					$where = 'r';
					break;
			}
			
			$this->legend_position = '&chdlp='.$where;
		}
	}
	
	public function setAxes($x="",$y="") {
		/*
			chxt=x,y,r
			chxl=1:|min|average|max
			chxp=1,10,35,75|2,0,1,2,4
			chxr=2,0,4
		*/
		if($x === YES) {
			$x="";
			$showX = YES;
		}
		
		if($y === YES) {
			$y = "";
			$showY = YES;
		}
		
		if(strlen($x) > 0 && strlen($y) > 0) {
			$this->axesString = "&chxt=x,y";
			$this->axesString .= "&chxl=" . "0:|" . $x . "|1:|" . $y;
		} elseif(strlen($x) > 0) {
			$this->axesString = "&chxt=x" . ($showY ? ",y" : "");
			$this->axesString .= "&chxl=" . "0:|".$x.($showY ? "1:" : "");
		} elseif(strlen($y) > 0) {
			$this->axesString = "&chxt="  . ($showX ? "x," : "") . "y";
			$this->axesString .= "&chxl=" . ($showX ? "0:1:|" : "1:|") . $y;
		}

	}
	
	protected function getBackgroundString() {
		if(!isset($this->backgrounds))
			return "";
		$retStr = "&chf=";
		foreach($this->backgrounds as $currBg) {
			$retStr .= $this->textEncodeData($currBg->toArray(), ",", "|"); 
		}
		$retStr = trim($retStr, "|");
		return $retStr;
	}
	protected function getAxisLabels() {
		$retStr = "";
		// if(isset($this->xAxisLabels))
			// $retStr = "&chxl=0:|".$this->encodeData($this->xAxisLabels,"", "|");
		return $retStr;
	}
	protected function concatUrl() {
		$fullUrl .= $this->baseUrl;
		$fullUrl .= "cht=".$this->types[$this->type];
		$fullUrl .= "&chs=".$this->width."x".$this->height;
		
		$fullUrl .= $this->getDataSetString();
		if(isset($this->valueLabels))
			$fullUrl .= "&chdl=".$this->encodeData($this->getApplicableLabels($this->valueLabels),"", "|");
		$fullUrl .= $this->getAxisLabels();
		$fullUrl .= "&chco=".$this->encodeData($this->dataColors,"", ",");
		if(isset($this->title))
			$fullUrl .= "&chtt=".$this->title;
		$fullUrl .= $this->getAxesString();
//		$fullUrl .= $this->getBackgroundString();
		$fullUrl .= $this->legend_position;		
		return $fullUrl;
	}
	
	protected function getApplicableLabels($labels) {
		$trimmedValueLabels = $labels;
		return $labels;
		return array_splice($trimmedValueLabels, 0, count($this->values)); // I know what this is trying to do, but it just isn't working...not sure why.
	}
	
	public function getUrl() {
		$this->prepForUrl();
		return $this->concatUrl();
	}
	
	protected function scaleValues() {
		$this->setScalar();
		$this->scaledValues = gchartutil::getScaledArray($this->values, $this->scalar);
	}

	function setScalar() {
		$maxValue = 100;
		$maxValue = max($maxValue, gchartutil::getMaxOfArray($this->values));
		if($maxValue <100)
			$this->scalar = 1;
		else
			$this->scalar = 100/$maxValue;
	}
}

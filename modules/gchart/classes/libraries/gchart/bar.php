<?php

/**
 * GChart Bar Chart subclass
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

class GChart_Bar extends GChart {
	public $barWidth;
	private $realBarWidth;
	public $groupSpacerWidth = 1;
	protected $totalBars = 1;
	protected $isHoriz = false;
	
	public function getUrl() {
		$this->scaleValues();
		$this->setBarWidth();
		$retStr = parent::concatUrl();
		$retStr .= '&chbh=a';
		return $retStr;
	}
	
	private function setBarCount() {
		$this->totalBars = gchartutil::count_r($this->values);
	}
	
	protected function getAxisLabels() {
		$retStr = "";
		$xAxis = 0;
		if($this->isHoriz)
			$xAxis = 1;	
		$yAxis = 1 - $xAxis;			
		if(isset($this->xAxisLabels)) {
			$retStr = "&chxl=$xAxis:|".$this->encodeData($this->xAxisLabels,"", "|");
//			$retStr = "&$yAxis:|".$this->encodeData($this->yAxisLabels,"", "|");
		}
		return $retStr;
	}
	
	private function setBarWidth() {
		if(isset($this->barWidth)) {
			$this->realBarWidth = $this->barWidth;
			return;
		}
		$this->setBarCount();
		$totalGroups = gchartutil::getMaxCountOfArray($this->values);
		if($this->isHoriz)
			$chartSize = $this->height - 50;
		else
			$chartSize = $this->width - 50;
			
		$chartSize -= $totalGroups * $this->groupSpacerWidth;
		$this->realBarWidth = round($chartSize/$this->totalBars);
	}
	
}

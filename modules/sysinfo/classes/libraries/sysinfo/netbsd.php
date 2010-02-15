<?php 

/**
 * @package		Modules
 * @subpackage	SystemInfo
 */

class SysInfo_NetBSD_Core extends SysInfo_BSD {
	protected $cpu_regexp;
	protected $scsi_regexp; 

	// Our contstructor
	// this function is run on the initialization of this class
	public function __construct() {
		parent::__construct();
		$this->cpu_regexp = "^cpu(.*)\, (.*) MHz";
		$this->scsi_regexp1 = "^(.*) at scsibus.*: <(.*)> .*";
		$this->scsi_regexp2 = "^(da[0-9]): (.*)MB ";
		$this->cpu_regexp2 = "/user = (.*), nice = (.*), sys = (.*), intr = (.*), idle = (.*)/";
		$this->pci_regexp1 = '/(.*) at pci[0-9] dev [0-9]* function [0-9]*: (.*)$/';
		$this->pci_regexp2 = '/"(.*)" (.*).* at [.0-9]+ irq/';
	} 

	public function get_sys_ticks() {
		$a = $this->grab_key('kern.boottime');
		$sys_ticks = time() - $a;
		return $sys_ticks;
	} 

	public function network() {
		$netstat_b = self::execute_program('netstat', '-nbdi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link"');
		$netstat_n = self::execute_program('netstat', '-ndi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link"');
		$lines_b = split("\n", $netstat_b);
		$lines_n = split("\n", $netstat_n);
		$results = array();
		for ($i = 0, $max = sizeof($lines_b); $i < $max; $i++) {
			$ar_buf_b = preg_split("/\s+/", $lines_b[$i]);
			$ar_buf_n = preg_split("/\s+/", $lines_n[$i]);
			if (!empty($ar_buf_b[0]) && !empty($ar_buf_n[3])) {
				$results[$ar_buf_b[0]] = array();

				$results[$ar_buf_b[0]]['rx_bytes'] = $ar_buf_b[3];
				$results[$ar_buf_b[0]]['rx_packets'] = $ar_buf_n[3];
				$results[$ar_buf_b[0]]['rx_errs'] = $ar_buf_n[4];
				$results[$ar_buf_b[0]]['rx_drop'] = $ar_buf_n[8];

				$results[$ar_buf_b[0]]['tx_bytes'] = $ar_buf_b[4];
				$results[$ar_buf_b[0]]['tx_packets'] = $ar_buf_n[5];
				$results[$ar_buf_b[0]]['tx_errs'] = $ar_buf_n[6];
				$results[$ar_buf_b[0]]['tx_drop'] = $ar_buf_n[8];

				$results[$ar_buf_b[0]]['errs'] = $ar_buf_n[4] + $ar_buf_n[6];
				$results[$ar_buf_b[0]]['drop'] = $ar_buf_n[8];
			} 
		} 
		return $results;
	} 

	// get the ide device information out of dmesg
	public function ide() {
		$results = array();

		$s = 0;
		for ($i = 0, $max = count($this->read_dmesg()); $i < $max; $i++) {
			$buf = $this->dmesg[$i];
			if (preg_match('/^(.*) at (pciide|wdc|atabus|atapibus)[0-9] (.*): <(.*)>/', $buf, $ar_buf)) {
				$s = $ar_buf[1];
				$results[$s]['model'] = $ar_buf[4];
				$results[$s]['media'] = 'Hard Disk'; 
				// now loop again and find the capacity
				for ($j = 0, $max1 = count($this->read_dmesg()); $j < $max1; $j++) {
					$buf_n = $this->dmesg[$j];
					if (preg_match("/^($s): (.*), (.*), (.*)MB, .*$/", $buf_n, $ar_buf_n)) {
						$results[$s]['capacity'] = $ar_buf_n[4] * 2048 * 1.049;
					} elseif (preg_match("/^($s): (.*) MB, (.*), (.*), .*$/", $buf_n, $ar_buf_n)) {
						$results[$s]['capacity'] = $ar_buf_n[2] * 2048;
					}
				} 
			} 
		} 
		asort($results);
		return $results;
	} 
} 

?>
